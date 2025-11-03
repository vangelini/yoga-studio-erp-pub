<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Course;
use App\Models\Subscription;
use App\Models\Teacher;
use App\Models\User;
use App\Support\TimeHelper;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function show(Request $request)
    {
        $payload = $request->validate([
            'user.id' => ['required', 'integer'],
            'user.role' => ['required', 'string'],
        ]);

        $user = User::find($payload['user']['id']);

        if (!$user) {
            return response()->json(['message' => 'Authentication required.'], 401);
        }

        $courses = Course::with(['teacher', 'schedule'])->get()->map(function (Course $course) {
            return [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'teacher_id' => $course->teacher_id,
                'teacherId' => $course->teacher_id,
                'teacher_name' => optional($course->teacher)->name,
                'teacherName' => optional($course->teacher)->name,
                'price' => $course->price,
                'speciality_description' => $course->speciality_description,
                'specialityDescription' => $course->speciality_description,
                'gallery' => $course->gallery ?? [],
                'start_date' => optional($course->start_date)?->format('Y-m-d'),
                'end_date' => optional($course->end_date)?->format('Y-m-d'),
                'startDate' => optional($course->start_date)?->format('Y-m-d'),
                'endDate' => optional($course->end_date)?->format('Y-m-d'),
                'startDateDisplay' => optional($course->start_date)?->format('d/m/Y'),
                'endDateDisplay' => optional($course->end_date)?->format('d/m/Y'),
                'schedule' => $course->schedule->map(function ($slot) {
                    return [
                        'day' => $slot->day_of_week,
                        'time' => TimeHelper::format($slot->time),
                    ];
                })->all(),
            ];
        });

        $teachers = Teacher::with(['user', 'availability.bookedBy'])->get()->map(function (Teacher $teacher) {
            return [
                'id' => $teacher->user->id,
                'name' => $teacher->user->name,
                'email' => $teacher->user->email,
                'telephone' => $teacher->user->telephone,
                'profile_picture_url' => $teacher->profile_picture_url,
                'bio' => $teacher->bio,
                'specializations' => $teacher->specializations ?? [],
                'availability' => $teacher->availability->map(function ($slot) {
                    return [
                        'date' => optional($slot->slot_date)->format('Y-m-d'),
                        'time' => TimeHelper::format($slot->slot_time),
                        'isBooked' => (bool) $slot->is_booked,
                        'bookedBy' => $slot->booked_by_client_id,
                        'bookedByName' => optional($slot->bookedBy)->name,
                    ];
                })->all(),
            ];
        });

        $response = [
            'courses' => $courses,
            'teachers' => $teachers,
        ];

        if ($user->role === 'Admin') {
            $response['users'] = User::orderBy('name')
                ->get(['id', 'name', 'email', 'role', 'status', 'telephone']);
        } elseif ($user->role === 'Client') {
            $clientBookings = Booking::with(['availability', 'teacher'])
                ->where('client_id', $user->id)
                ->get()
                ->map(function (Booking $booking) {
                    $slot = $booking->availability;
                    $teacher = $booking->teacher;
                    return [
                        'id' => $booking->id,
                        'clientId' => $booking->client_id,
                        'teacherId' => $booking->teacher_id,
                        'teacher' => $teacher ? [
                            'id' => $teacher->id,
                            'name' => $teacher->name,
                            'email' => $teacher->email,
                            'telephone' => $teacher->telephone,
                        ] : null,
                        'slot' => [
                            'date' => optional($slot->slot_date)->format('Y-m-d'),
                            'time' => TimeHelper::format($slot->slot_time),
                            'isBooked' => (bool) $slot->is_booked,
                            'bookedBy' => $slot->booked_by_client_id,
                            'bookedByName' => optional($slot->bookedBy)->name,
                        ],
                    ];
                });

            $subscriptions = Subscription::where('client_id', $user->id)->get();

            $response['bookings'] = $clientBookings;
            $response['subscriptions'] = $subscriptions->map(function (Subscription $subscription) {
                $subscription->loadMissing('course:id,title,price');

                return [
                    'id' => $subscription->id,
                    'client_id' => $subscription->client_id,
                    'clientId' => $subscription->client_id,
                    'course_id' => $subscription->course_id,
                    'courseId' => $subscription->course_id,
                    'auto_renew' => (bool) $subscription->auto_renew,
                    'autoRenew' => (bool) $subscription->auto_renew,
                    'start_date' => optional($subscription->start_date)?->format('Y-m-d'),
                    'startDate' => optional($subscription->start_date)?->format('Y-m-d'),
                    'startDateDisplay' => optional($subscription->start_date)?->translatedFormat('d/m/Y'),
                    'course' => optional($subscription->course)?->only(['id', 'title', 'price']),
                ];
            });
        } elseif ($user->role === 'Teacher') {
            $teacherBookings = Booking::with(['availability', 'client'])
                ->where('teacher_id', $user->id)
                ->get()
                ->map(function (Booking $booking) {
                    $slot = $booking->availability;
                    $client = $booking->client;
                    return [
                        'id' => $booking->id,
                        'clientId' => $booking->client_id,
                        'teacherId' => $booking->teacher_id,
                        'client' => $client ? [
                            'id' => $client->id,
                            'name' => $client->name,
                            'email' => $client->email,
                            'telephone' => $client->telephone,
                        ] : null,
                        'slot' => [
                            'date' => optional($slot->slot_date)->format('Y-m-d'),
                            'time' => TimeHelper::format($slot->slot_time),
                            'isBooked' => (bool) $slot->is_booked,
                            'bookedBy' => $slot->booked_by_client_id,
                            'bookedByName' => optional($slot->bookedBy)->name,
                        ],
                    ];
                });

            $clients = User::query()
                ->select('users.id', 'users.name', 'users.email', 'users.status', 'users.telephone')
                ->join('bookings', 'users.id', '=', 'bookings.client_id')
                ->where('bookings.teacher_id', $user->id)
                ->where('users.role', 'Client')
                ->distinct()
                ->orderBy('users.name')
                ->get();

            $response['bookings'] = $teacherBookings;
            $response['clients'] = $clients;
        }

        return response()->json($response);
    }
}
