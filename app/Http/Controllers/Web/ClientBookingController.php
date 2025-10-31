<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\TeacherAvailability;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ClientBookingController extends Controller
{
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $client = $request->user();
        abort_unless($client && $client->role === 'Client', 403);

        $data = $request->validate([
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'availability_id' => ['required', 'integer', 'exists:teacher_availability,id'],
        ]);

        $bookingRecord = null;
        $availabilityRecord = null;
        $paymentRecord = null;

        DB::transaction(function () use ($data, $client, &$bookingRecord, &$availabilityRecord, &$paymentRecord) {
            $availability = TeacherAvailability::whereKey($data['availability_id'])
                ->lockForUpdate()
                ->first();

            if (!$availability || $availability->teacher_id !== (int) $data['teacher_id']) {
                throw ValidationException::withMessages([
                    'availability_id' => 'The selected slot is no longer available.',
                ]);
            }

            if ($availability->is_booked) {
                throw ValidationException::withMessages([
                    'availability_id' => 'This slot has already been booked by another client.',
                ]);
            }

            $lessonDateTime = $this->slotDateTime($availability);
            if ($lessonDateTime->lessThanOrEqualTo(now())) {
                throw ValidationException::withMessages([
                    'availability_id' => 'You can only book future time slots.',
                ]);
            }

            $availability->update([
                'is_booked' => true,
                'booked_by_client_id' => $client->id,
            ]);

            $bookingRecord = Booking::create([
                'client_id' => $client->id,
                'teacher_id' => $availability->teacher_id,
                'availability_id' => $availability->id,
            ]);

            $bookingRecord->load('teacher:id,name');
            $availabilityRecord = $availability->fresh();

            $paymentRecord = Payment::create([
                'user_id' => $client->id,
                'payable_type' => Booking::class,
                'payable_id' => $bookingRecord->id,
                'type' => 'private_lesson',
                'amount' => 0,
                'status' => 'pending',
                'due_date' => optional($availabilityRecord->slot_date),
                'meta' => [
                    'teacher_id' => $availabilityRecord->teacher_id,
                    'slot' => $availabilityRecord->slot_date ? $availabilityRecord->slot_date->format('Y-m-d') : null,
                ],
            ]);
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Lesson booked successfully.',
                'booking' => $this->formatBookingPayload($bookingRecord, $availabilityRecord),
                'slot' => [
                    'id' => $availabilityRecord->id,
                    'teacher_id' => $availabilityRecord->teacher_id,
                ],
                'payment' => $paymentRecord ? $this->formatPaymentPayload($paymentRecord) : null,
            ], 201);
        }

        return redirect()
            ->route('dashboard')
            ->with('status', 'Lesson booked successfully.');
    }

    public function destroy(Request $request, Booking $booking): RedirectResponse|JsonResponse
    {
        $client = $request->user();
        abort_unless($client && $client->role === 'Client', 403);
        abort_unless($booking->client_id === $client->id, 403);

        $availabilityRecord = null;
        $bookingPayload = null;

        $payment = null;

        DB::transaction(function () use ($booking, &$payment) {
            $availability = TeacherAvailability::lockForUpdate()->find($booking->availability_id);

            if (!$availability) {
                throw ValidationException::withMessages([
                    'booking' => 'The booking could not be found.',
                ]);
            }

            $lessonDateTime = $this->slotDateTime($availability, 'booking');
            $minutesUntilLesson = now()->diffInMinutes($lessonDateTime, false);

            if ($minutesUntilLesson < 24 * 60) {
                throw ValidationException::withMessages([
                    'booking' => 'Bookings can only be cancelled up to 24 hours in advance.',
                ]);
            }

            $availability->update([
                'is_booked' => false,
                'booked_by_client_id' => null,
            ]);

            $payment = Payment::where('payable_type', Booking::class)
                ->where('payable_id', $booking->id)
                ->first();

            if ($payment) {
                $payment->delete();
            }

            $booking->delete();
        });

        $availabilityRecord = TeacherAvailability::find($booking->availability_id);

        if ($availabilityRecord) {
            $bookingPayload = $this->formatBookingPayload($booking, $availabilityRecord);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Booking cancelled successfully.',
                'booking_id' => $booking->id,
                'slot' => $availabilityRecord ? [
                    'id' => $availabilityRecord->id,
                    'teacher_id' => $availabilityRecord->teacher_id,
                ] : null,
                'booking' => $bookingPayload,
                'payment_id' => $payment?->id,
            ]);
        }

        return redirect()
            ->route('dashboard')
            ->with('status', 'Booking cancelled successfully.');
    }

    protected function slotDateTime(TeacherAvailability $availability, string $errorKey = 'availability_id'): Carbon
    {
        $date = optional($availability->slot_date)?->format('Y-m-d');
        $time = optional($availability->slot_time)?->format('H:i:s');

        if (!$date || !$time) {
            throw ValidationException::withMessages([
                $errorKey => 'This slot is missing scheduling information.',
            ]);
        }

        return Carbon::parse("{$date} {$time}");
    }

    protected function formatBookingPayload(Booking $booking, TeacherAvailability $availability): array
    {
        $booking->loadMissing('teacher:id,name');

        return [
            'id' => $booking->id,
            'clientId' => $booking->client_id,
            'teacherId' => $booking->teacher_id,
            'teacher' => optional($booking->teacher)?->only(['id', 'name']),
            'date' => optional($availability->slot_date)?->format('Y-m-d'),
            'time' => optional($availability->slot_time)?->format('H:i'),
            'slot' => [
                'id' => $availability->id,
                'date' => optional($availability->slot_date)?->format('Y-m-d'),
                'time' => optional($availability->slot_time)?->format('H:i'),
            ],
        ];
    }

    protected function formatPaymentPayload(Payment $payment): array
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
