<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\CourseSubscriptionManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ClientSubscriptionController extends Controller
{
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $client = $request->user();
        abort_unless($client && $client->role === 'Client', 403);

        $data = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'plan_type' => ['required', Rule::in(['monthly', 'quarterly', 'annual'])],
            'start_option' => ['required', Rule::in(['current_month', 'next_month'])],
            'start_date' => ['nullable', 'date', 'required_if:start_option,current_month'],
        ]);

        $course = Course::findOrFail($data['course_id']);

        $selectedPrice = $course->getPlanPrice($data['plan_type']);
        if (is_null($selectedPrice) || $selectedPrice <= 0) {
            throw ValidationException::withMessages([
                'plan_type' => __('Il piano selezionato non è disponibile per questo corso.'),
            ]);
        }
        $startDateInput = $data['start_option'] === 'current_month' ? ($data['start_date'] ?? null) : null;

        /** @var CourseSubscriptionManager $manager */
        $manager = app(CourseSubscriptionManager::class);

        try {
            $result = $manager->createSubscription(
                $client,
                $course,
                $data['plan_type'],
                $data['start_option'],
                $startDateInput
            );
        } catch (ValidationException $exception) {
            throw $exception;
        }

        /** @var Subscription $subscription */
        $subscription = $result['subscription'];
        /** @var Payment|null $paymentRecord */
        $paymentRecord = $result['payment'] ?? null;

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Subscription activated.',
                'subscription' => $this->formatSubscription($subscription),
                'payment' => $paymentRecord ? $this->formatPayment($paymentRecord) : null,
            ], 201);
        }

        return redirect()
            ->route('dashboard')
            ->with('status', "You are now subscribed to {$course->title}.");
    }

    public function toggleRenewal(Request $request, Subscription $subscription): RedirectResponse|JsonResponse
    {
        $client = $request->user();
        abort_unless($client && $client->role === 'Client', 403);
        abort_unless($subscription->client_id === $client->id, 403);

        $subscription->update([
            'auto_renew' => !$subscription->auto_renew,
        ]);

        $subscription->load('course:id,title,price,monthly_price,quarterly_price,annual_price');

        $message = $subscription->auto_renew
            ? 'Auto renew enabled.'
            : 'Auto renew disabled.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'subscription' => $this->formatSubscription($subscription),
            ]);
        }

        return redirect()
            ->route('dashboard')
            ->with('status', $message);
    }

    public function destroy(Request $request, Subscription $subscription): RedirectResponse|JsonResponse
    {
        $client = $request->user();
        abort_unless($client && $client->role === 'Client', 403);
        abort_unless($subscription->client_id === $client->id, 403);

        $payment = Payment::where('payable_type', Subscription::class)
            ->where('payable_id', $subscription->id)
            ->first();

        $subscription->delete();

        if ($payment) {
            $payment->delete();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Subscription cancelled.',
                'subscription_id' => $subscription->id,
                'payment_id' => $payment?->id,
            ]);
        }

        return redirect()
            ->route('dashboard')
            ->with('status', 'Subscription cancelled.');
    }

    protected function formatSubscription(Subscription $subscription): array
    {
        return [
            'id' => $subscription->id,
            'course_id' => $subscription->course_id,
            'courseId' => $subscription->course_id,
            'client_id' => $subscription->client_id,
            'clientId' => $subscription->client_id,
            'auto_renew' => (bool) $subscription->auto_renew,
            'autoRenew' => (bool) $subscription->auto_renew,
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
        ];
    }

    protected function formatPayment(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'type' => $payment->type,
            'status' => $payment->status,
            'amount' => $payment->amount,
             'amount_formatted' => number_format((float) $payment->amount, 2, '.', ''),
            'due_date' => optional($payment->due_date)?->format('Y-m-d'),
            'paid_at' => optional($payment->paid_at)?->format('Y-m-d H:i'),
            'method' => $payment->method,
            'meta' => $payment->meta ?? [],
        ];
    }
}
