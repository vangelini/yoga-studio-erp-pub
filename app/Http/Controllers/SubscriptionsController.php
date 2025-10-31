<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionsController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'clientId' => ['required', 'integer', 'exists:users,id'],
            'courseId' => ['required', 'integer', 'exists:courses,id'],
        ]);

        $existing = Subscription::where('client_id', $data['clientId'])
            ->where('course_id', $data['courseId'])
            ->first();

        if ($existing) {
            return response()->json(['message' => 'You are already subscribed to this course.'], 409);
        }

        $subscription = Subscription::create([
            'client_id' => $data['clientId'],
            'course_id' => $data['courseId'],
            'auto_renew' => true,
        ])->load('course:id,title,price');

        return response()->json([
            'subscription' => [
                'id' => $subscription->id,
                'course_id' => $subscription->course_id,
                'auto_renew' => (bool) $subscription->auto_renew,
                'course' => optional($subscription->course)?->only(['id', 'title', 'price']),
            ],
        ], 201);
    }

    public function toggleRenewal(Subscription $subscription)
    {
        $subscription->update([
            'auto_renew' => !$subscription->auto_renew,
        ]);

        $subscription->load('course:id,title,price');

        return response()->json([
            'subscription' => [
                'id' => $subscription->id,
                'course_id' => $subscription->course_id,
                'auto_renew' => (bool) $subscription->auto_renew,
                'course' => optional($subscription->course)?->only(['id', 'title', 'price']),
            ],
        ]);
    }
}
