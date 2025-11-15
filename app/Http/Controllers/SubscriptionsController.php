<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Subscription;
use App\Models\User;
use App\Services\CourseSubscriptionManager;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SubscriptionsController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'clientId' => ['required', 'integer', 'exists:users,id'],
            'courseId' => ['required', 'integer', 'exists:courses,id'],
            'planType' => ['required', Rule::in(['monthly', 'quarterly', 'annual'])],
            'startOption' => ['required', Rule::in(['current_month', 'next_month'])],
            'startDate' => ['nullable', 'date', 'required_if:startOption,current_month'],
            'selectedLessons' => ['nullable', 'array'],
            'selectedLessons.*' => ['integer'],
        ]);

        $client = User::findOrFail($data['clientId']);
        $course = Course::findOrFail($data['courseId']);

        $lessonBased = ($course->pricing_mode ?? 'block') === 'per_lesson';
        $selectedLessons = collect($data['selectedLessons'] ?? [])
            ->map(fn ($value) => is_numeric($value) ? (int) $value : null)
            ->filter(fn ($value) => !is_null($value))
            ->unique()
            ->values()
            ->all();

        if ($lessonBased) {
            $lessonPricing = $course->lesson_pricing[$data['planType']] ?? [];
            if (empty($lessonPricing)) {
                throw ValidationException::withMessages([
                    'planType' => __('Non è stato configurato un prezzo per questo piano.'),
                ]);
            }
            if (empty($selectedLessons)) {
                throw ValidationException::withMessages([
                    'selectedLessons' => __('Seleziona almeno una lezione disponibile.'),
                ]);
            }
        } else {
            $selectedPrice = $course->getPlanPrice($data['planType']);
            if (is_null($selectedPrice) || $selectedPrice <= 0) {
                throw ValidationException::withMessages([
                    'planType' => __('Il piano selezionato non è disponibile per questo corso.'),
                ]);
            }
        }

        $startDateInput = $data['startOption'] === 'current_month' ? ($data['startDate'] ?? null) : null;

        /** @var CourseSubscriptionManager $manager */
        $manager = app(CourseSubscriptionManager::class);

        try {
            $result = $manager->createSubscription(
                $client,
                $course,
                $data['planType'],
                $data['startOption'],
                $startDateInput,
                null,
                $selectedLessons
            );
        } catch (ValidationException $exception) {
            $errors = $exception->errors();
            return response()->json([
                'message' => reset($errors)[0] ?? 'Validation error.',
                'errors' => $errors,
            ], 422);
        }

        $subscription = $result['subscription'];
        $payment = $result['payment'];

        return response()->json([
            'subscription' => [
                'id' => $subscription->id,
                'course_id' => $subscription->course_id,
                'courseId' => $subscription->course_id,
                'client_id' => $subscription->client_id,
                'clientId' => $subscription->client_id,
                'auto_renew' => (bool) $subscription->auto_renew,
                'autoRenew' => (bool) $subscription->auto_renew,
                'status' => $subscription->status,
                'cancelled_at' => optional($subscription->cancelled_at)?->toIso8601String(),
                'cancelledAt' => optional($subscription->cancelled_at)?->toIso8601String(),
                'cancelledAtDisplay' => optional($subscription->cancelled_at)?->translatedFormat('d/m/Y H:i'),
                'start_date' => optional($subscription->start_date)?->format('Y-m-d'),
                'startDate' => optional($subscription->start_date)?->format('Y-m-d'),
                'startDateDisplay' => optional($subscription->start_date)?->translatedFormat('d/m/Y'),
                'end_date' => optional($subscription->end_date)?->format('Y-m-d'),
                'endDate' => optional($subscription->end_date)?->format('Y-m-d'),
                'endDateDisplay' => optional($subscription->end_date)?->translatedFormat('d/m/Y'),
                'plan_type' => $subscription->plan_type,
                'planType' => $subscription->plan_type,
                'plan_label' => $subscription->plan_label,
                'planLabel' => $subscription->plan_label,
                'plan_amount' => $subscription->plan_amount,
                'planAmount' => $subscription->plan_amount,
                'course' => optional($subscription->course)?->only([
                    'id',
                    'title',
                    'price',
                    'monthly_price',
                    'quarterly_price',
                    'annual_price',
                ]),
            ],
            'payment' => $payment ? [
                'id' => $payment->id,
                'type' => $payment->type,
                'status' => $payment->status,
                'amount' => $payment->amount,
                'amount_formatted' => number_format((float) $payment->amount, 2, '.', ''),
                'due_date' => optional($payment->due_date)?->format('Y-m-d'),
                'paid_at' => optional($payment->paid_at)?->format('Y-m-d H:i'),
                'method' => $payment->method,
                'meta' => $payment->meta ?? [],
            ] : null,
        ], 201);
    }
}
