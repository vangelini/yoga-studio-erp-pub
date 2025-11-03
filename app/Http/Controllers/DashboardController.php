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
                'teacher_name' => optional($course->teacher)->name,
                'price' => $course->price,
                'speciality_description' => $course->speciality_description,
                'gallery' => $course->gallery ?? [],
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
            $response['subscriptions'] = $subscriptions;
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
