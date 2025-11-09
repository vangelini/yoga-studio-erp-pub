<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\MembershipSubscription;
use App\Models\Payment;
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
        abort_unless($request->user()->role === 'Admin', 403);

        $data = $request->validate([
            'action' => ['required', Rule::in(['cash', 'waive'])],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        if ($data['action'] === 'waive') {
            $request->validate(['reason' => ['required', 'string', 'max:255']]);
        }

        if ($data['action'] === 'cash') {
            $payment->markAsPaid('cash');

            if ($payment->type === 'membership' && $payment->payable instanceof MembershipSubscription) {
                $payment->payable->update([
                    'status' => 'active',
                    'paid_at' => $payment->paid_at,
                ]);
            }

            $receiptPath = $this->receiptService->generate($payment, $data['reason'] ?? null);
            $message = 'Pagamento registrato in contanti.';
        } else {
            $payment->markAsWaived($data['reason']);
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
        abort_unless($request->user()->role === 'Admin', 403);

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

        if ($user->role === 'Teacher' && $payment->payable_type === Booking::class) {
            $booking = $payment->payable;
            if ($booking && (int) $booking->teacher_id === (int) $user->id) {
                return true;
            }
        }

        return false;
    }
}
