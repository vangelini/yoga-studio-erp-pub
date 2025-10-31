<?php

namespace App\Services;

use App\Models\MembershipSubscription;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;

class MembershipManager
{
    public function ensureCurrentMembership(User $user): MembershipSubscription
    {
        $season = $this->determineCurrentSeason();

        $membership = MembershipSubscription::firstOrCreate(
            [
                'user_id' => $user->id,
                'season_start_year' => $season['start_year'],
            ],
            [
                'starts_at' => $season['starts_at'],
                'ends_at' => $season['ends_at'],
                'status' => 'pending',
                'amount' => config('app.membership_fee', 0),
                'due_date' => $season['starts_at'],
            ]
        );

        $payment = Payment::firstOrCreate(
            [
                'user_id' => $user->id,
                'payable_type' => MembershipSubscription::class,
                'payable_id' => $membership->id,
                'type' => 'membership',
            ],
            [
                'amount' => $membership->amount,
                'status' => $membership->status === 'active' ? 'paid' : 'pending',
                'due_date' => $membership->due_date,
                'paid_at' => $membership->paid_at,
                'meta' => [
                    'season' => $membership->season_start_year.'/'.($membership->season_start_year + 1),
                ],
            ]
        );

        if ($membership->relationLoaded('payment')) {
            $membership->setRelation('payment', $payment);
        } else {
            $membership->load('payment');
        }

        return $membership;
    }

    public function determineCurrentSeason(?Carbon $reference = null): array
    {
        $now = $reference ?? Carbon::now();
        $year = (int) $now->format('Y');
        $seasonStart = Carbon::create($year, 9, 1);

        if ($now->lt($seasonStart)) {
            $startYear = $year - 1;
            $seasonStart = Carbon::create($startYear, 9, 1);
        } else {
            $startYear = $year;
        }

        $seasonEnd = Carbon::create($startYear + 1, 8, 31);

        return [
            'start_year' => $startYear,
            'starts_at' => $seasonStart,
            'ends_at' => $seasonEnd,
        ];
    }
}
