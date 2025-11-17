<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Course;
use App\Models\MembershipSubscription;
use App\Models\Payment;
use App\Models\Subscription;
use App\Services\CashReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PaymentAdminController extends Controller
{
    public function __construct(
        private CashReceiptService $receiptService,
    ) {
    }

    public function updateStatus(Request $request, Payment $payment)
    {
        $this->authorizePaymentManagement($request, $payment);

        $data = $request->validate([
            'action' => ['required', Rule::in(['cash', 'bank_transfer', 'waive'])],
            'reason' => ['nullable', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'note' => ['nullable', 'string', 'max:500'],
            'transfer_reference' => ['nullable', 'string', 'max:255', 'required_if:action,bank_transfer'],
        ]);

        if ($data['action'] === 'waive') {
            $request->validate(['reason' => ['required', 'string', 'max:255']]);
        }

        if (in_array($data['action'], ['cash', 'bank_transfer'], true)) {
            if (!empty($data['amount'])) {
                $payment->amount = (float) $data['amount'];
            }

            $meta = $payment->meta ?? [];
            if (!empty($data['note'])) {
                $meta['manual_note'] = $data['note'];
            }
            if (!empty($data['amount'])) {
                $meta['manual_amount'] = (float) $data['amount'];
            }
            if (!empty($data['transfer_reference'])) {
                $meta['transfer_reference'] = $data['transfer_reference'];
            }
            if (!empty($meta)) {
                $payment->meta = $meta;
            }

            $payment->markAsPaid($data['action'], $request->user()->id);

            if ($payment->type === 'membership' && $payment->payable instanceof MembershipSubscription) {
                $payment->payable->update([
                    'status' => 'active',
                    'paid_at' => $payment->paid_at,
                ]);
            }

            $receiptNote = $data['note'] ?? ($data['reason'] ?? null);
            if (!empty($data['transfer_reference'])) {
                $receiptNote = trim(($receiptNote ? $receiptNote . ' | ' : '') . 'CRO/Bonifico: ' . $data['transfer_reference']);
            }

            $receiptPath = $this->receiptService->generate($payment, $receiptNote);
            $message = $data['action'] === 'bank_transfer'
                ? 'Pagamento registrato come bonifico.'
                : 'Pagamento registrato in contanti.';
        } else {
            $payment->markAsWaived($data['reason'], $request->user()->id);
            $message = 'Mese annullato con successo.';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'payment' => [
                    'id' => $payment->id,
                    'status' => $payment->status,
                    'status_reason' => $payment->status_reason,
                    'paid_at' => optional($payment->paid_at)?->format('Y-m-d H:i'),
                    'method' => $payment->method,
                    'receipt_url' => $payment->receipt_url,
                ],
            ]);
        }

        return back()->with('status', $message);
    }

    public function reprint(Request $request, Payment $payment)
    {
        $this->authorizePaymentManagement($request, $payment);

        $data = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->receiptService->reprint($payment, $data['notes'] ?? null);
        $payment->refresh();

        $message = 'Ricevuta ristampata con successo.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'payment' => [
                    'id' => $payment->id,
                    'receipt_url' => $payment->receipt_url,
                    'receipt_number' => $payment->receipt_number,
                    'receipt_year' => $payment->receipt_year,
                ],
            ]);
        }

        return back()->with('status', $message);
    }

    public function showReceipt(Request $request, Payment $payment): BinaryFileResponse
    {
        $payment->loadMissing('payable');

        abort_unless($this->canAccessReceipt($request->user(), $payment), 403);

        if (!$payment->receipt_path || !Storage::disk(config('receipt.storage_disk', 'public'))->exists($payment->receipt_path)) {
            abort(404);
        }

        $absolutePath = Storage::disk(config('receipt.storage_disk', 'public'))->path($payment->receipt_path);

        return response()->file($absolutePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($absolutePath) . '"',
        ]);
    }

    private function authorizePaymentManagement(Request $request, Payment $payment): void
    {
        $user = $request->user();
        abort_unless($user, 403);

        if ($user->role === 'Admin') {
            return;
        }

        if ($user->role === 'Teacher') {
            $teacher = $user->teacherProfile;
            abort_unless($teacher && $teacher->can_manage_payments, 403);

            $payment->loadMissing('payable');

            if ($payment->type === 'course_subscription') {
                abort_unless(
                    $payment->course_id && Course::where('id', $payment->course_id)->where('teacher_id', $user->id)->exists(),
                    403
                );
                return;
            }

            if ($payment->type === 'membership') {
                abort_unless($this->teacherHasStudent($user->id, $payment->user_id), 403);
                return;
            }

            if ($payment->type === 'private_lesson' && $payment->payable_type === Booking::class) {
                $booking = $payment->payable;
                if (!$booking) {
                    $booking = $payment->payable()->first();
                }
                abort_unless($booking && (int) $booking->teacher_id === (int) $user->id, 403);
                return;
            }
        }

        abort(403);
    }

    private function teacherHasStudent(int $teacherId, ?int $studentId): bool
    {
        if (!$studentId) {
            return false;
        }

        return Subscription::where('client_id', $studentId)
            ->whereHas('course', function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })
            ->exists();
    }

    private function canAccessReceipt($user, Payment $payment): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->role === 'Admin') {
            return true;
        }

        if ($payment->user_id === $user->id) {
            return true;
        }

        if ($user->role === 'Teacher') {
            $teacherProfile = $user->teacherProfile;

            if ($payment->payable_type === Booking::class) {
                $booking = $payment->payable;
                if ($booking && (int) $booking->teacher_id === (int) $user->id) {
                    return true;
                }
            }

            if ($teacherProfile && $teacherProfile->can_manage_payments) {
                if ($payment->type === 'course_subscription' && $this->teacherOwnsCoursePayment($user->id, $payment)) {
                    return true;
                }

                if ($payment->type === 'membership' && $this->teacherHasStudent($user->id, $payment->user_id)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function teacherOwnsCoursePayment(int $teacherId, Payment $payment): bool
    {
        if (!$payment->course_id) {
            return false;
        }

        return Course::where('id', $payment->course_id)
            ->where('teacher_id', $teacherId)
            ->exists();
    }
}
