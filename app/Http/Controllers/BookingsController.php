<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\TeacherAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingsController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'clientId' => ['required', 'integer', 'exists:users,id'],
            'teacherId' => ['required', 'integer', 'exists:users,id'],
            'slot.date' => ['required', 'date_format:Y-m-d'],
            'slot.time' => ['required', 'date_format:H:i'],
        ]);

        return DB::transaction(function () use ($data) {
            $availability = TeacherAvailability::where('teacher_id', $data['teacherId'])
                ->whereDate('slot_date', $data['slot']['date'])
                ->whereTime('slot_time', $data['slot']['time'])
                ->lockForUpdate()
                ->first();

            if (!$availability) {
                return response()->json(['message' => 'This time slot does not exist.'], 404);
            }

            if ($availability->is_booked) {
                return response()->json(['message' => 'This slot has just been booked. Please choose another.'], 409);
            }

            $availability->update([
                'is_booked' => true,
                'booked_by_client_id' => $data['clientId'],
            ]);

            $booking = Booking::create([
                'client_id' => $data['clientId'],
                'teacher_id' => $data['teacherId'],
                'availability_id' => $availability->id,
            ]);

            $teacher = $booking->teacher()->select('id', 'name')->first();

            return response()->json([
                'message' => 'Booking successful!',
                'booking' => [
                    'id' => $booking->id,
                    'clientId' => $booking->client_id,
                    'teacherId' => $booking->teacher_id,
                    'teacher' => $teacher ? $teacher->toArray() : null,
                    'slot' => $data['slot'],
                ],
            ], 201);
        });
    }

    public function destroy(Booking $booking)
    {
        return DB::transaction(function () use ($booking) {
            $availability = TeacherAvailability::lockForUpdate()->find($booking->availability_id);

            if (!$availability) {
                return response()->json(['message' => 'Booking not found.'], 404);
            }

            $lessonDate = Carbon::parse(
                $availability->slot_date->format('Y-m-d') . ' ' . $availability->slot_time->format('H:i')
            );

            if ($lessonDate->diffInHours(now(), false) >= -24) {
                return response()->json([
                    'message' => 'Cannot cancel a lesson less than 24 hours in advance.',
                ], 403);
            }

            $availability->update([
                'is_booked' => false,
                'booked_by_client_id' => null,
            ]);

            $booking->delete();

            return response()->json(['message' => 'Booking cancelled successfully.']);
        });
    }
}
