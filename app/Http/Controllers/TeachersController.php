<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\TeacherAvailability;
use App\Models\User;
use App\Support\TimeHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeachersController extends Controller
{
    public function updateProfile(Request $request, User $teacher)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'profilePictureUrl' => ['nullable', 'string'],
            'bio' => ['nullable', 'string'],
            'specializations' => ['nullable', 'array'],
            'specializations.*' => ['string'],
        ]);

        return DB::transaction(function () use ($teacher, $data) {
            $teacher->update(['name' => $data['name']]);

            $profile = Teacher::firstOrCreate(
                ['user_id' => $teacher->id],
                ['specializations' => []]
            );

            $profile->update([
                'profile_picture_url' => $data['profilePictureUrl'] ?? $profile->profile_picture_url,
                'bio' => $data['bio'] ?? $profile->bio,
                'specializations' => $data['specializations'] ?? $profile->specializations,
            ]);

            return response()->json(['message' => 'Profile updated successfully.']);
        });
    }

    public function updateAvailability(Request $request, User $teacher)
    {
        $data = $request->validate([
            'availability' => ['required', 'array'],
            'availability.*.date' => ['required', 'date_format:Y-m-d'],
            'availability.*.time' => ['required', 'date_format:H:i'],
        ]);

        return DB::transaction(function () use ($teacher, $data) {
            TeacherAvailability::where('teacher_id', $teacher->id)
                ->where('is_booked', false)
                ->delete();

            foreach ($data['availability'] as $slot) {
                TeacherAvailability::firstOrCreate(
                    [
                        'teacher_id' => $teacher->id,
                        'slot_date' => $slot['date'],
                        'slot_time' => $slot['time'],
                    ],
                    [
                        'is_booked' => false,
                    ]
                );
            }

            $availability = TeacherAvailability::where('teacher_id', $teacher->id)
                ->with('bookedBy')
                ->get()
                ->map(function (TeacherAvailability $slot) {
                    return [
                        'date' => optional($slot->slot_date)->format('Y-m-d'),
                        'time' => TimeHelper::format($slot->slot_time),
                        'isBooked' => (bool) $slot->is_booked,
                        'bookedBy' => $slot->booked_by_client_id,
                        'bookedByName' => optional($slot->bookedBy)->name,
                    ];
                });

            return response()->json([
                'availability' => $availability,
            ]);
        });
    }
}
