<?php

namespace App\Services;

use App\Models\MembershipSubscription;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Setting;

class MembershipManager
{
    public function ensureCurrentMembership(User $user, bool $createPayment = true): MembershipSubscription
    {
        $amountSetting = Setting::query()->find('membership_fee');
        $amount = (float) ($amountSetting?->value ?? config('app.membership_fee', 20));

        if ($this->currentMode() === 'rolling') {
            return $this->ensureRollingMembership($user, $amount, $createPayment);
        }

        return $this->ensureAcademicMembership($user, $amount, $createPayment);
    }

    public function determineCurrentSeason(?Carbon $reference = null): array
    {
        $now = $reference ?? Carbon::now();
        if ($this->currentMode() === 'rolling') {
            return $this->calculateRollingSeason($now->copy()->startOfDay());
        }

        return $this->calculateAcademicSeason($now);
    }

    public function currentMode(): string
    {
        $setting = Setting::query()->find('membership_expiry_mode');
        return $setting && $setting->value === 'rolling' ? 'rolling' : 'academic';
    }

    protected function calculateAcademicSeason(Carbon $reference): array
    {
        $now = $reference->copy();
        $startSetting = Setting::query()->find('membership_academic_start_date')?->value;
        $endSetting = Setting::query()->find('membership_academic_end_date')?->value;

        $defaultStart = Carbon::create($now->year, 9, 1);
        $defaultEnd = Carbon::create($now->year, 8, 31)->addYear();

        try {
            $startTemplate = $startSetting ? Carbon::parse($startSetting) : $defaultStart;
        } catch (\Exception $e) {
            $startTemplate = $defaultStart;
        }

        try {
            $endTemplate = $endSetting ? Carbon::parse($endSetting) : $defaultEnd;
        } catch (\Exception $e) {
            $endTemplate = $defaultEnd;
        }

        $seasonStart = Carbon::create($now->year, $startTemplate->month, $startTemplate->day);
        if ($now->lt($seasonStart)) {
            $seasonStart->subYear();
        }

        $seasonEnd = Carbon::create($seasonStart->year, $endTemplate->month, $endTemplate->day);
        if ($seasonEnd->lte($seasonStart)) {
            $seasonEnd->addYear();
        }

        $startYear = (int) $seasonStart->format('Y');

        return [
            'start_year' => $startYear,
            'starts_at' => $seasonStart,
            'ends_at' => $seasonEnd,
            'due_date' => $seasonStart->copy(),
            'label' => sprintf('%s - %s', $seasonStart->format('d/m/Y'), $seasonEnd->format('d/m/Y')),
        ];
    }

    public function calculateRollingSeason(Carbon $startReference): array
    {
        $start = $startReference->copy()->startOfDay();
        $end = $start->copy()->addYear()->subDay();

        $startYear = (int) $start->format('Y');

        return [
            'start_year' => $startYear,
            'starts_at' => $start,
            'ends_at' => $end,
            'due_date' => $start->copy(),
            'label' => sprintf('%s - %s', $start->format('d/m/Y'), $end->format('d/m/Y')),
        ];
    }

    protected function ensureAcademicMembership(User $user, float $amount, bool $createPayment): MembershipSubscription
    {
        $season = $this->calculateAcademicSeason(Carbon::now());

        $membership = MembershipSubscription::firstOrCreate(
            [
                'user_id' => $user->id,
                'season_start_year' => $season['start_year'],
            ],
            [
                'starts_at' => $season['starts_at'],
                'ends_at' => $season['ends_at'],
                'status' => 'pending',
                'amount' => $amount,
                'due_date' => $season['due_date'],
            ]
        );

        $membership->fill([
            'season_start_year' => $season['start_year'],
            'starts_at' => $season['starts_at'],
            'ends_at' => $season['ends_at'],
            'due_date' => $season['due_date'],
        ]);

        if ($membership->amount != $amount) {
            $membership->amount = $amount;
        }

        if ($membership->isDirty()) {
            $membership->save();
        }

        $this->syncMembershipPayment($membership, $amount, $createPayment, $season);

        return $membership;
    }

    protected function ensureRollingMembership(User $user, float $amount, bool $createPayment): MembershipSubscription
    {
        $latest = MembershipSubscription::query()
            ->where('user_id', $user->id)
            ->orderByDesc('ends_at')
            ->first();

        $today = Carbon::now()->startOfDay();

        if ($latest && $latest->ends_at && $latest->ends_at->gte($today) && $latest->status !== 'expired') {
            if ($latest->amount != $amount) {
                $latest->amount = $amount;
            }
            if (!$latest->due_date) {
                $latest->due_date = $latest->starts_at ?? $today;
            }
            if ($latest->isDirty()) {
                $latest->save();
            }

            $season = [
                'start_year' => (int) ($latest->starts_at?->format('Y') ?? $today->year),
                'starts_at' => $latest->starts_at ?? $today,
                'ends_at' => $latest->ends_at ?? $today->copy()->addYear()->subDay(),
                'due_date' => $latest->due_date ?? ($latest->starts_at ?? $today),
                'label' => sprintf(
                    '%s - %s',
                    ($latest->starts_at ?? $today)->format('d/m/Y'),
                    ($latest->ends_at ?? $today->copy()->addYear()->subDay())->format('d/m/Y')
                ),
            ];

            $this->syncMembershipPayment($latest, $amount, $createPayment, $season);

            return $latest;
        }

        $start = $latest && $latest->ends_at
            ? $latest->ends_at->copy()->addDay()->startOfDay()
            : $today;

        $season = $this->calculateRollingSeason($start);

        $membership = MembershipSubscription::create([
            'user_id' => $user->id,
            'season_start_year' => $season['start_year'],
            'starts_at' => $season['starts_at'],
            'ends_at' => $season['ends_at'],
            'status' => 'pending',
            'amount' => $amount,
            'due_date' => $season['due_date'],
        ]);

        $this->syncMembershipPayment($membership, $amount, $createPayment, $season);

        return $membership;
    }

    protected function syncMembershipPayment(MembershipSubscription $membership, float $amount, bool $createPayment, array $season): void
    {
        if (!$createPayment) {
            $membership->load('payment');
            return;
        }

        $payment = Payment::firstOrCreate(
            [
                'user_id' => $membership->user_id,
                'payable_type' => MembershipSubscription::class,
                'payable_id' => $membership->id,
                'type' => 'membership',
            ],
            [
                'amount' => $amount,
                'status' => 'pending',
                'due_date' => $season['due_date'],
                'paid_at' => $membership->paid_at,
                'receipt_year' => $season['start_year'],
                'meta' => [
                    'season' => $season['label'],
                ],
            ]
        );

        if ($payment->status === 'pending') {
            $updates = [
                'amount' => $amount,
                'due_date' => $season['due_date'],
                'receipt_year' => $season['start_year'],
                'meta' => [
                    'season' => $season['label'],
                ],
            ];

            $payment->fill($updates);

            if ($payment->isDirty()) {
                $payment->save();
            }
        }

        $membership->setRelation('payment', $payment);
    }
}
