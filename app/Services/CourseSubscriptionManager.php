<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\SubscriptionLesson;
use App\Models\User;
use App\Support\TimeHelper;
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
        ?string $startDateInput = null,
        ?Course $extraCourse = null,
        array $selectedLessonIds = []
    ): array
    {
        $planType = in_array($planType, array_keys(Course::PLAN_MONTHS), true) ? $planType : 'monthly';
        $startOption = in_array($startOption, ['current_month', 'next_month', 'annual_fixed'], true) ? $startOption : 'current_month';
        $today = Carbon::today(config('app.timezone'));

        $existing = Subscription::where('client_id', $client->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing && $existing->status !== 'cancelled') {
            throw ValidationException::withMessages([
                'course_id' => __('Sei già iscritto a questo corso.'),
            ]);
        }

        $course->loadMissing(['schedule']);
        $pricingMode = $course->pricing_mode ?? 'block';
        $selectedLessonIds = collect($selectedLessonIds ?? [])
            ->map(fn ($value) => is_numeric($value) ? (int) $value : null)
            ->filter(fn ($value) => !is_null($value))
            ->unique()
            ->values()
            ->all();
        $selectedLessonSlots = [];
        $lessonDayOverrides = null;

        if ($pricingMode === 'per_lesson') {
            if (empty($selectedLessonIds)) {
                throw ValidationException::withMessages([
                    'selected_lessons' => __('Seleziona almeno una lezione settimanale per il corso.'),
                ]);
            }

            $scheduleById = $course->schedule->keyBy('id');
            foreach ($selectedLessonIds as $scheduleId) {
                $slot = $scheduleById->get($scheduleId);
                if (!$slot) {
                    throw ValidationException::withMessages([
                        'selected_lessons' => __('Una delle lezioni selezionate non è valida per questo corso.'),
                    ]);
                }
                $selectedLessonSlots[] = $slot;
            }

            $lessonsCount = count($selectedLessonSlots);
            $lessonPricing = $this->normalizeLessonPricing($course->lesson_pricing[$planType] ?? []);
            $basePrice = $lessonPricing[$lessonsCount] ?? null;

            if (is_null($basePrice) || $basePrice <= 0) {
                throw ValidationException::withMessages([
                    'selected_lessons' => __('Non è stato configurato un prezzo per il numero di lezioni selezionate.'),
                ]);
            }

            $lessonDayOverrides = array_map(fn ($slot) => $slot->day_of_week, $selectedLessonSlots);
        } else {
            $basePrice = (float) ($course->getPlanPrice($planType) ?? 0);
            if ($basePrice <= 0) {
                throw ValidationException::withMessages([
                    'plan_type' => __('Questo piano non è disponibile per il corso selezionato.'),
                ]);
            }
        }

        $this->assertMaxEnrollments($course, $existing);
        if ($pricingMode === 'per_lesson') {
            $this->assertLessonCapacity($course, $selectedLessonSlots, $existing);
        }

        $lessonSnapshot = $this->formatLessonSnapshot($selectedLessonSlots);
        $durationMonths = Course::PLAN_MONTHS[$planType] ?? 1;
        $startDate = null;
        $endDate = null;
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
            'pricing_mode' => $pricingMode,
        ];
        if (!empty($lessonSnapshot)) {
            $meta['selected_lessons'] = $lessonSnapshot;
            $meta['lessons_per_week'] = count($lessonSnapshot);
        }
        $periodStart = null;
        $periodEnd = null;

        if ($planType === 'annual') {
            $season = $this->calculateAcademicWindow($today);
            $startDate = $season['starts_at']->copy();
            $endDate = $season['ends_at']->copy();
            $amount = round($basePrice, 2);
            if ($basePrice > 0 && $amount < 0.01) {
                $amount = 0.01;
            }
            $meta = array_merge($meta, [
                'start_option' => 'academic_year',
                'start_date' => $startDate->toDateString(),
                'subscription_start' => $startDate->toDateString(),
                'subscription_end' => $endDate->toDateString(),
                'prorated' => false,
            ]);
        } elseif ($startOption === 'current_month') {
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
                $lessonProration = $this->calculateLessonProration($course, $periodStart, $periodEnd, $startDate, $lessonDayOverrides);

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

        $endDate = $endDate ?? ($startDate ? $startDate->copy()->addMonthsNoOverflow($durationMonths)->subDay() : null);
        $extraData = $this->prepareExtraDayData(
            $extraCourse,
            $durationMonths,
            $planType,
            $startOption,
            $startDate,
            $periodStart,
            $periodEnd
        );

        if ($extraData) {
            $amount += $extraData['charged_amount'];
            $meta['extra_day'] = array_merge(
                $extraData['snapshot'],
                [
                    'plan_amount' => $extraData['plan_amount'],
                    'charged_amount' => $extraData['charged_amount'],
                ]
            );
        }

        return DB::transaction(function () use ($client, $course, $startDate, $endDate, $amount, $meta, $planType, $basePrice, $existing, $extraCourse, $extraData, $selectedLessonSlots) {
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
                    'extra_course_id' => $extraCourse?->id,
                    'extra_course_plan_amount' => $extraData['plan_amount'] ?? null,
                    'extra_course_snapshot' => $extraData['snapshot'] ?? null,
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
                    'extra_course_id' => $extraCourse?->id,
                    'extra_course_plan_amount' => $extraData['plan_amount'] ?? null,
                    'extra_course_snapshot' => $extraData['snapshot'] ?? null,
                ]);
            }

            $subscription->lessons()->delete();
            foreach ($selectedLessonSlots as $slot) {
                $subscription->lessons()->create([
                    'course_schedule_id' => $slot->id,
                    'day_of_week' => $this->mapDayOfWeek($slot->day_of_week) ?? 0,
                    'time' => $slot->time ? $slot->time->format('H:i:s') : null,
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

            $subscription->load([
                'course:id,title,price,monthly_price,quarterly_price,annual_price',
                'extraCourse:id,title,monthly_price,teacher_id',
                'lessons.schedule',
            ]);
            $payment->refresh();

            return [
                'subscription' => $subscription,
                'payment' => $payment,
            ];
        });
    }

    private function calculateAcademicWindow(Carbon $reference): array
    {
        $startSetting = Setting::query()->find('membership_academic_start_date')?->value;
        $endSetting = Setting::query()->find('membership_academic_end_date')?->value;

        $defaultStart = Carbon::create($reference->year, 9, 1);
        $defaultEnd = Carbon::create($reference->year, 8, 31)->addYear();

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

        $seasonStart = Carbon::create($reference->year, $startTemplate->month, $startTemplate->day);
        if ($reference->lt($seasonStart)) {
            $seasonStart->subYear();
        }

        $seasonEnd = Carbon::create($seasonStart->year, $endTemplate->month, $endTemplate->day);
        if ($seasonEnd->lte($seasonStart)) {
            $seasonEnd->addYear();
        }

        return [
            'starts_at' => $seasonStart,
            'ends_at' => $seasonEnd,
            'due_date' => $seasonStart->copy(),
        ];
    }

    protected function assertMaxEnrollments(Course $course, ?Subscription $existing = null): void
    {
        if (!$course->max_enrollments) {
            return;
        }

        $query = Subscription::query()
            ->where('course_id', $course->id)
            ->where('status', '!=', 'cancelled');

        if ($existing) {
            $query->where('id', '!=', $existing->id);
        }

        $count = (int) $query->count();
        if ($count >= $course->max_enrollments) {
            throw ValidationException::withMessages([
                'course_id' => __('Questo corso ha raggiunto il numero massimo di iscritti.'),
            ]);
        }
    }

    protected function assertLessonCapacity(Course $course, array $selectedSlots, ?Subscription $existing = null): void
    {
        $slotsWithCapacity = collect($selectedSlots)
            ->filter(fn ($slot) => !is_null($slot->capacity))
            ->values();

        if ($slotsWithCapacity->isEmpty()) {
            return;
        }

        $ids = $slotsWithCapacity->pluck('id')->all();
        $usage = SubscriptionLesson::query()
            ->select('course_schedule_id', DB::raw('count(*) as aggregate'))
            ->whereIn('course_schedule_id', $ids)
            ->whereHas('subscription', function ($query) use ($course, $existing) {
                $query->where('course_id', $course->id)
                    ->where('status', '!=', 'cancelled');
                if ($existing) {
                    $query->where('id', '!=', $existing->id);
                }
            })
            ->groupBy('course_schedule_id')
            ->pluck('aggregate', 'course_schedule_id');

        foreach ($slotsWithCapacity as $slot) {
            $used = (int) ($usage[$slot->id] ?? 0);
            if ($used >= $slot->capacity) {
                $label = trim($slot->day_of_week . ' ' . ($slot->time ? TimeHelper::format($slot->time) : ''));
                throw ValidationException::withMessages([
                    'selected_lessons' => __('La lezione :label non ha più posti disponibili.', [
                        'label' => $label ?: __('selezionata'),
                    ]),
                ]);
            }
        }
    }

    protected function normalizeLessonPricing($pricing): array
    {
        $normalized = [];
        foreach ((array) $pricing as $lessons => $amount) {
            $count = (int) $lessons;
            if ($count <= 0) {
                continue;
            }

            if ($amount === null || $amount === '') {
                continue;
            }
            $value = (float) $amount;
            if ($value <= 0) {
                continue;
            }
            $normalized[$count] = round($value, 2);
        }

        return $normalized;
    }

    protected function formatLessonSnapshot(array $slots): array
    {
        if (empty($slots)) {
            return [];
        }

        return collect($slots)
            ->map(function ($slot) {
                $time = $slot->time ? TimeHelper::format($slot->time) : null;
                $label = trim(($slot->day_of_week ?? '') . ' ' . ($time ?? ''));
                return [
                    'course_schedule_id' => $slot->id,
                    'day' => $slot->day_of_week,
                    'time' => $time,
                    'label' => $label,
                ];
            })
            ->values()
            ->all();
    }

    protected function prepareExtraDayData(
        ?Course $extraCourse,
        int $planMonths,
        string $planType,
        string $startOption,
        ?Carbon $startDate,
        ?Carbon $periodStart,
        ?Carbon $periodEnd
    ): ?array {
        if (!$extraCourse) {
            return null;
        }

        $extraCourse->loadMissing(['schedule', 'teacher']);

        $monthlyPrice = (float) ($extraCourse->monthly_price ?? $extraCourse->price ?? 0);
        $lessonsPerWeek = $extraCourse->schedule ? $extraCourse->schedule->count() : 0;

        if ($monthlyPrice <= 0 || $lessonsPerWeek <= 0) {
            throw ValidationException::withMessages([
                'extra_course_id' => __('Non è possibile calcolare il costo della lezione extra per il corso selezionato.'),
            ]);
        }

        $unitLessonAmount = round($monthlyPrice / max(1, $lessonsPerWeek), 2);
        $basePlanAmount = round($unitLessonAmount * max(1, $planMonths), 2);

        $discountPercent = max(0, min(100, (float) ($extraCourse->extra_day_discount_percent ?? 0)));
        $discountAmount = round($basePlanAmount * ($discountPercent / 100), 2);
        $planAmount = max($basePlanAmount - $discountAmount, 0);
        if ($planAmount > 0 && $planAmount < 0.01) {
            $planAmount = 0.01;
        }

        $chargedAmount = $planAmount;
        $snapshot = [
            'course_id' => $extraCourse->id,
            'course_title' => $extraCourse->title,
            'teacher_id' => $extraCourse->teacher_id,
            'teacher_name' => optional($extraCourse->teacher)->name,
            'weekly_lessons' => $lessonsPerWeek,
            'monthly_price' => $monthlyPrice,
            'unit_lesson_amount' => $unitLessonAmount,
            'plan_months' => $planMonths,
            'base_plan_amount' => $basePlanAmount,
            'discount_percent' => $discountPercent,
            'discount_amount' => $discountAmount,
        ];

        if (
            $planType === 'monthly'
            && $startOption === 'current_month'
            && $periodStart
            && $periodEnd
            && $startDate
        ) {
            $lessonProration = $this->calculateLessonProration($extraCourse, $periodStart, $periodEnd, $startDate);

            if ($lessonProration['total'] > 0 && $lessonProration['remaining'] >= 0) {
                $ratio = $lessonProration['total'] > 0 ? $lessonProration['remaining'] / $lessonProration['total'] : 1;
                $chargedAmount = round($planAmount * $ratio, 2);
                if ($planAmount > 0 && $chargedAmount < 0.01) {
                    $chargedAmount = 0.01;
                }

                $snapshot['prorated'] = true;
                $snapshot['proration_ratio'] = $ratio;
                $snapshot['proration_details'] = $lessonProration;
            }
        }

        return [
            'plan_amount' => $planAmount,
            'charged_amount' => $chargedAmount,
            'snapshot' => $snapshot,
        ];
    }

    protected function calculateLessonProration(Course $course, Carbon $periodStart, Carbon $periodEnd, Carbon $clientStart, ?array $overrideDays = null): array
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

        if ($overrideDays !== null) {
            $schedules = collect($overrideDays)->map(function ($day) {
                return (object) ['day_of_week' => $day];
            });
        } else {
            $schedules = $course->schedule()->get(['day_of_week']);
        }

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
