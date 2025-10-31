<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PaymentAdminController extends Controller
{
    public function updateStatus(Request $request, Payment $payment): RedirectResponse
    {
        abort_unless($request->user()->role === 'Admin', 403);

        $data = $request->validate([
            'action' => ['required', Rule::in(['cash', 'waive'])],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        if ($data['action'] === 'cash') {
            $payment->markAsPaid('cash');
            $payment->status_reason = $data['reason'];
            $payment->save();
            $message = 'Pagamento registrato in contanti.';
        } else {
            $payment->forceFill([
                'status' => 'waived',
                'status_reason' => $data['reason'],
                'paid_at' => null,
                'method' => null,
            ])->save();
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
                ],
            ]);
        }

        return back()->with('status', $message);
    }
}
