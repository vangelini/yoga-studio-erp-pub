<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CourseSubscriptionManager
{
    /**
     * @return array{subscription: Subscription, payment: ?Payment}
     *
     * @throws ValidationException
     */
    public function createSubscription(
        User $client,
        Course $course,
        string $planType,
        string $startOption,
        ?string $startDateInput = null
    ): array
    {
        $planType = in_array($planType, array_keys(Course::PLAN_MONTHS), true) ? $planType : 'monthly';
        $startOption = $startOption === 'next_month' ? 'next_month' : 'current_month';
        $today = Carbon::today(config('app.timezone'));

        $existing = Subscription::where('client_id', $client->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing && $existing->status !== 'cancelled') {
            throw ValidationException::withMessages([
                'course_id' => __('Sei già iscritto a questo corso.'),
            ]);
        }

        $basePrice = (float) ($course->getPlanPrice($planType) ?? 0);
        if ($basePrice <= 0) {
            throw ValidationException::withMessages([
                'plan_type' => __('Questo piano non è disponibile per il corso selezionato.'),
            ]);
        }

        $durationMonths = Course::PLAN_MONTHS[$planType] ?? 1;
        $startDate = null;
        $amount = $basePrice;
        $meta = [
            'course_title' => $course->title,
            'base_price' => $basePrice,
            'plan_type' => $planType,
            'plan_months' => $durationMonths,
            'plan_label' => match ($planType) {
                'quarterly' => __('Trimestrale'),
                'annual' => __('Annuale'),
                default => __('Mensile'),
            },
            'plan_amount' => $basePrice,
        ];

        if ($startOption === 'current_month') {
            $startDate = $startDateInput ? Carbon::parse($startDateInput, $today->timezone) : $today->copy();
            $startDate = $startDate->startOfDay();

            if ($startDate->lt($today)) {
                throw ValidationException::withMessages([
                    'start_date' => __('La data di inizio non può essere nel passato.'),
                ]);
            }

            if ($startDate->month !== $today->month || $startDate->year !== $today->year) {
                throw ValidationException::withMessages([
                    'start_date' => __('La data di inizio deve essere nel mese corrente.'),
                ]);
            }

            $endOfMonth = $today->copy()->endOfMonth();
            if ($startDate->gt($endOfMonth)) {
                throw ValidationException::withMessages([
                    'start_date' => __('La data di inizio deve essere entro la fine del mese corrente.'),
                ]);
            }
            if ($planType === 'monthly') {
                $periodStart = $today->copy()->startOfMonth();
                $periodEnd = $today->copy()->endOfMonth();
                $lessonProration = $this->calculateLessonProration($course, $periodStart, $periodEnd, $startDate);

                if ($lessonProration['total'] > 0 && $lessonProration['remaining'] > 0) {
                    $ratio = $lessonProration['remaining'] / $lessonProration['total'];
                    $amount = round($basePrice * $ratio, 2);

                    if ($basePrice > 0 && $amount < 0.01) {
                        $amount = 0.01;
                    }

                    $meta = array_merge($meta, [
                        'start_option' => 'current_month',
                        'start_date' => $startDate->toDateString(),
                        'prorated' => true,
                        'proration_basis' => 'lessons',
                        'remaining_lessons' => $lessonProration['remaining'],
                        'total_lessons' => $lessonProration['total'],
                        'proration_ratio' => $ratio,
                        'proration_details' => $lessonProration['details'],
                    ]);
                } else {
                    $daysInMonth = $startDate->daysInMonth;
                    $remainingDays = $daysInMonth - $startDate->day + 1;
                    $ratio = $remainingDays / $daysInMonth;
                    $amount = round($basePrice * $ratio, 2);

                    if ($basePrice > 0 && $amount < 0.01) {
                        $amount = 0.01;
                    }

                    $meta = array_merge($meta, [
                        'start_option' => 'current_month',
                        'start_date' => $startDate->toDateString(),
                        'prorated' => true,
                        'proration_basis' => 'days',
                        'remaining_days' => $remainingDays,
                        'days_in_month' => $daysInMonth,
                        'proration_ratio' => $ratio,
                    ]);
                }
            } else {
                $amount = round($basePrice, 2);

                if ($basePrice > 0 && $amount < 0.01) {
                    $amount = 0.01;
                }

                $meta = array_merge($meta, [
                    'start_option' => 'current_month',
                    'start_date' => $startDate->toDateString(),
                    'prorated' => false,
                ]);
            }
        } else {
            $startDate = $today->copy()->addMonthNoOverflow()->startOfMonth();
            $amount = round($basePrice, 2);

            if ($basePrice > 0 && $amount < 0.01) {
                $amount = 0.01;
            }

            $meta = array_merge($meta, [
                'start_option' => 'next_month',
                'start_date' => $startDate->toDateString(),
                'prorated' => false,
            ]);
        }

        $endDate = $startDate ? $startDate->copy()->addMonthsNoOverflow($durationMonths)->subDay() : null;

        return DB::transaction(function () use ($client, $course, $startDate, $endDate, $amount, $meta, $planType, $basePrice, $existing) {
            /** @var Subscription $subscription */
            if ($existing) {
                $existing->fill([
                    'auto_renew' => true,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'plan_type' => $planType,
                    'plan_amount' => $basePrice,
                    'status' => 'active',
                    'cancelled_at' => null,
                ]);
                $existing->save();
                $subscription = $existing->fresh();
            } else {
                $subscription = Subscription::create([
                    'client_id' => $client->id,
                    'course_id' => $course->id,
                    'auto_renew' => true,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'plan_type' => $planType,
                    'plan_amount' => $basePrice,
                    'status' => 'active',
                    'cancelled_at' => null,
                ]);
            }

            $payment = Payment::create([
                'user_id' => $client->id,
                'payable_type' => Subscription::class,
                'payable_id' => $subscription->id,
                'course_id' => $course->id,
                'type' => 'course_subscription',
                'amount' => $amount,
                'status' => 'pending',
                'due_date' => $startDate,
                'receipt_year' => $startDate ? (int) $startDate->format('Y') : now()->year,
                'meta' => $meta,
            ]);

            $subscription->load('course:id,title,price,monthly_price,quarterly_price,annual_price');
            $payment->refresh();

            return [
                'subscription' => $subscription,
                'payment' => $payment,
            ];
        });
    }

    protected function calculateLessonProration(Course $course, Carbon $periodStart, Carbon $periodEnd, Carbon $clientStart): array
    {
        $courseStart = $course->start_date?->copy();
        $courseEnd = $course->end_date?->copy();

        $effectiveStart = $periodStart->copy();
        if ($courseStart && $courseStart->gt($effectiveStart)) {
            $effectiveStart = $courseStart->copy();
        }

        $effectiveEnd = $periodEnd->copy();
        if ($courseEnd && $courseEnd->lt($effectiveEnd)) {
            $effectiveEnd = $courseEnd->copy();
        }

        if ($effectiveStart->gt($effectiveEnd)) {
            return ['total' => 0, 'remaining' => 0, 'details' => []];
        }

        $remainingStart = $clientStart->copy();
        if ($remainingStart->lt($effectiveStart)) {
            $remainingStart = $effectiveStart->copy();
        }

        if ($remainingStart->gt($effectiveEnd)) {
            return ['total' => 0, 'remaining' => 0, 'details' => []];
        }

        $schedules = $course->schedule()->get(['day_of_week']);
        if ($schedules->isEmpty()) {
            return ['total' => 0, 'remaining' => 0, 'details' => []];
        }

        $totalLessons = 0;
        $remainingLessons = 0;
        $details = [];

        foreach ($schedules as $slot) {
            $weekday = $this->mapDayOfWeek($slot->day_of_week);
            if ($weekday === null) {
                continue;
            }

            $totalForSlot = $this->countWeekdayOccurrences($effectiveStart, $effectiveEnd, $weekday);
            $remainingForSlot = $this->countWeekdayOccurrences($remainingStart, $effectiveEnd, $weekday);

            $totalLessons += $totalForSlot;
            $remainingLessons += $remainingForSlot;

            $details[] = [
                'day' => $slot->day_of_week,
                'weekday' => $weekday,
                'total' => $totalForSlot,
                'remaining' => $remainingForSlot,
            ];
        }

        return [
            'total' => $totalLessons,
            'remaining' => $remainingLessons,
            'details' => $details,
        ];
    }

    protected function mapDayOfWeek(?string $label): ?int
    {
        if (!$label) {
            return null;
        }

        $map = [
            'Lunedì' => Carbon::MONDAY,
            'Martedì' => Carbon::TUESDAY,
            'Mercoledì' => Carbon::WEDNESDAY,
            'Giovedì' => Carbon::THURSDAY,
            'Venerdì' => Carbon::FRIDAY,
            'Sabato' => Carbon::SATURDAY,
            'Domenica' => Carbon::SUNDAY,
            'Lunedi' => Carbon::MONDAY,
            'Martedi' => Carbon::TUESDAY,
            'Mercoledi' => Carbon::WEDNESDAY,
            'Giovedi' => Carbon::THURSDAY,
            'Venerdi' => Carbon::FRIDAY,
            'Sabato' => Carbon::SATURDAY,
            'Domenica' => Carbon::SUNDAY,
            'Monday' => Carbon::MONDAY,
            'Tuesday' => Carbon::TUESDAY,
            'Wednesday' => Carbon::WEDNESDAY,
            'Thursday' => Carbon::THURSDAY,
            'Friday' => Carbon::FRIDAY,
            'Saturday' => Carbon::SATURDAY,
            'Sunday' => Carbon::SUNDAY,
        ];

        return $map[$label] ?? null;
    }

    protected function countWeekdayOccurrences(Carbon $start, Carbon $end, int $weekday): int
    {
        $first = $start->copy();
        if ($first->dayOfWeek !== $weekday) {
            $first = $first->next($this->weekdayName($weekday));
        }

        if ($first->gt($end)) {
            return 0;
        }

        $last = $end->copy();
        if ($last->dayOfWeek !== $weekday) {
            $last = $last->previous($this->weekdayName($weekday));
        }

        if ($first->gt($last)) {
            return 0;
        }

        return intdiv($first->diffInDays($last), 7) + 1;
    }

    private function weekdayName(int $weekday): string
    {
        return match ($weekday) {
            Carbon::MONDAY => 'Monday',
            Carbon::TUESDAY => 'Tuesday',
            Carbon::WEDNESDAY => 'Wednesday',
            Carbon::THURSDAY => 'Thursday',
            Carbon::FRIDAY => 'Friday',
            Carbon::SATURDAY => 'Saturday',
            Carbon::SUNDAY => 'Sunday',
        };
    }
}
