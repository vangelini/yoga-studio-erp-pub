<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClientSubscriptionController extends Controller
{
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $client = $request->user();
        abort_unless($client && $client->role === 'Client', 403);

        $data = $request->validate([
            'course_id' => ['required', 'integer', 'exists:courses,id'],
        ]);

        $course = Course::findOrFail($data['course_id']);

        $paymentRecord = null;

        $subscription = Subscription::firstOrCreate(
            [
                'client_id' => $client->id,
                'course_id' => $course->id,
            ],
            [
                'auto_renew' => true,
            ]
        );

        $subscription->load('course:id,title,price');

        if ($subscription->wasRecentlyCreated) {
            $paymentRecord = Payment::create([
                'user_id' => $client->id,
                'payable_type' => Subscription::class,
                'payable_id' => $subscription->id,
                'type' => 'course_subscription',
                'amount' => $subscription->course->price ?? 0,
                'status' => 'pending',
                'due_date' => now(),
                'meta' => [
                    'course_title' => $subscription->course->title,
                ],
            ]);
        }

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

        $subscription->load('course:id,title,price');

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
            'client_id' => $subscription->client_id,
            'auto_renew' => (bool) $subscription->auto_renew,
            'course' => optional($subscription->course)?->only(['id', 'title', 'price']),
        ];
    }

    protected function formatPayment(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'type' => $payment->type,
            'status' => $payment->status,
            'amount' => $payment->amount,
            'due_date' => optional($payment->due_date)?->format('Y-m-d'),
            'paid_at' => optional($payment->paid_at)?->format('Y-m-d H:i'),
            'method' => $payment->method,
            'meta' => $payment->meta ?? [],
        ];
    }
}
