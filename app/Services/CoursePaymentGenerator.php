<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Subscription;
use App\Support\TimeHelper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CoursePaymentGenerator
{
    /**
     * Generate upcoming course subscription payments.
     *
     * @param  int  $leadDays
     * @param  int|null  $teacherId
     * @return array{checked:int,created:int,skipped:int,run_at:string}
     */
    public function generate(int $leadDays = 10, ?int $teacherId = null): array
    {
        $leadDays = max(0, $leadDays);
        $now = now();

        $subscriptions = Subscription::with([
                'course:id,title,monthly_price,quarterly_price,annual_price,price,teacher_id,pricing_mode',
                'extraCourse:id,title,monthly_price,teacher_id',
                'lessons.schedule',
            ])
            ->where('auto_renew', true)
            ->where('status', 'active');

        if ($teacherId) {
            $subscriptions->whereHas('course', function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            });
        }

        $subscriptions = $subscriptions->get();

        $created = 0;
        $skipped = 0;
        $createdIds = [];

        foreach ($subscriptions as $subscription) {
            $payment = $this->ensureUpcomingPayment($subscription, $leadDays, $now);
            if ($payment) {
                $created++;
                $createdIds[] = $payment->id;
            } else {
                $skipped++;
            }
        }

        return [
            'checked' => $subscriptions->count(),
            'created' => $created,
            'created_ids' => $createdIds,
            'skipped' => $skipped,
            'run_at' => $now->toDateTimeString(),
        ];
    }

    protected function ensureUpcomingPayment(Subscription $subscription, int $leadDays, Carbon $now): ?Payment
    {
        if ($subscription->status !== 'active') {
            return null;
        }

        if (!$subscription->course_id || !$subscription->course) {
            return null;
        }

        $planMonths = max($subscription->planMonths(), 1);

        $lastPayment = Payment::query()
            ->where('payable_type', Subscription::class)
            ->where('payable_id', $subscription->id)
            ->where('type', 'course_subscription')
            ->orderByDesc('due_date')
            ->orderByDesc('id')
            ->first();

        if (!$lastPayment) {
            return null;
        }

        $lastDue = $lastPayment->due_date
            ? Carbon::parse($lastPayment->due_date)
            : ($subscription->start_date ? $subscription->start_date->copy() : $now->copy());

        $nextDue = $lastDue->copy()->addMonthsNoOverflow($planMonths);

        $leadDate = $nextDue->copy()->subDays($leadDays);

        if ($now->lt($leadDate)) {
            return null;
        }

        $existing = Payment::query()
            ->where('payable_type', Subscription::class)
            ->where('payable_id', $subscription->id)
            ->where('type', 'course_subscription')
            ->whereDate('due_date', '>=', $nextDue->toDateString())
            ->whereIn('status', ['pending', 'paid'])
            ->exists();

        if ($existing) {
            return null;
        }

        $amount = $subscription->plan_amount
            ?? $subscription->course?->getPlanPrice($subscription->plan_type)
            ?? $subscription->course?->price
            ?? 0;

        if ($amount <= 0) {
            return null;
        }

        $cycleStart = $nextDue->copy();
        $cycleEnd = $cycleStart->copy()->addMonthsNoOverflow($planMonths)->subDay();

        $meta = [
            'course_title' => $subscription->course?->title,
            'plan_type' => $subscription->plan_type,
            'plan_label' => $subscription->plan_label,
            'plan_months' => $planMonths,
            'renewal_cycle_start' => $cycleStart->toDateString(),
            'renewal_cycle_end' => $cycleEnd->toDateString(),
            'auto_generated' => true,
            'pricing_mode' => $subscription->course?->pricing_mode ?? 'block',
        ];

        $lessonMeta = $this->mapSubscriptionLessons($subscription);
        if (!empty($lessonMeta)) {
            $meta['selected_lessons'] = $lessonMeta;
            $meta['lessons_per_week'] = count($lessonMeta);
        }

        $extraMeta = null;
        if ($subscription->hasExtraDay() && ($subscription->extra_course_plan_amount ?? 0) > 0) {
            $amount += $subscription->extra_course_plan_amount;
            $extraSnapshot = $subscription->extra_course_snapshot ?? [
                'course_id' => $subscription->extra_course_id,
                'course_title' => optional($subscription->extraCourse)->title,
                'plan_months' => $planMonths,
            ];
            $extraMeta = array_merge($extraSnapshot, [
                'charged_amount' => $subscription->extra_course_plan_amount,
                'plan_amount' => $subscription->extra_course_plan_amount,
            ]);
            $meta['extra_day'] = $extraMeta;
        }

        $payment = null;

        DB::transaction(function () use ($subscription, $amount, $nextDue, $meta, &$payment) {
            $payment = Payment::create([
                'user_id' => $subscription->client_id,
                'payable_type' => Subscription::class,
                'payable_id' => $subscription->id,
                'course_id' => $subscription->course_id,
                'type' => 'course_subscription',
                'amount' => round($amount, 2),
                'status' => 'pending',
                'due_date' => $nextDue,
                'receipt_year' => (int) $nextDue->format('Y'),
                'meta' => $meta,
            ]);
        });

        return $payment;
    }

    protected function mapSubscriptionLessons(Subscription $subscription): array
    {
        $lessons = $subscription->relationLoaded('lessons')
            ? $subscription->lessons
            : $subscription->lessons()->with('schedule')->get();

        if ($lessons->isEmpty()) {
            return [];
        }

        return $lessons->map(function ($lesson) {
            $day = $lesson->schedule->day_of_week ?? null;
            $timeValue = $lesson->schedule->time ?? $lesson->time;
            $time = $timeValue ? TimeHelper::format($timeValue) : null;
            $label = trim(($day ?? '') . ' ' . ($time ?? ''));

            return [
                'course_schedule_id' => $lesson->course_schedule_id,
                'day' => $day,
                'time' => $time,
                'label' => $label,
            ];
        })->values()->all();
    }
}
