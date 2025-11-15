<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class AdminAccountingController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'Admin', 403);

        $status = $request->get('status');
        $from = $request->get('from');
        $to = $request->get('to');
        $sort = $request->get('sort', 'paid_at');
        $dir = strtolower($request->get('dir', 'desc')) === 'asc' ? 'asc' : 'desc';

        $query = Payment::with(['user:id,name,email', 'processedBy:id,name'])
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($from, function ($q) use ($from) {
                $q->whereDate('paid_at', '>=', $from);
            })
            ->when($to, function ($q) use ($to) {
                $q->whereDate('paid_at', '<=', $to);
            });

        $allowedSorts = ['id', 'user_id', 'amount', 'status', 'paid_at', 'due_date', 'type', 'receipt_year'];
        $sortColumn = in_array($sort, $allowedSorts, true) ? $sort : 'paid_at';

        $payments = $query
            ->orderBy($sortColumn, $dir)
            ->paginate(25)
            ->appends($request->query());

        return view('admin.accounting.index', [
            'payments' => $payments,
            'status' => $status,
            'from' => $from,
            'to' => $to,
            'sort' => $sortColumn,
            'dir' => $dir,
        ]);
    }

    public function exportReceipts(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'Admin', 403);

        $year = (int) $request->get('year', now()->year);

        $payments = Payment::whereNotNull('receipt_path')
            ->where('receipt_year', $year)
            ->get();

        if ($payments->isEmpty()) {
            return redirect()
                ->route('admin.accounting.index')
                ->with('status', "Nessuna ricevuta trovata per l'anno {$year}.");
        }

        $zip = new ZipArchive();
        $zipFilename = storage_path('app/tmp_receipts_' . $year . '_' . time() . '.zip');

        if ($zip->open($zipFilename, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()
                ->route('admin.accounting.index')
                ->with('status', 'Impossibile creare lo ZIP delle ricevute.');
        }

        foreach ($payments as $payment) {
            $path = $payment->receipt_path;
            if (!$path || !Storage::disk('public')->exists($path)) {
                continue;
            }
            $absolute = Storage::disk('public')->path($path);
            $name = 'receipt_' . $payment->id . '.pdf';
            $zip->addFile($absolute, $name);
        }

        $zip->close();

        return response()->download($zipFilename)->deleteFileAfterSend(true);
    }

    public function export(Request $request): StreamedResponse
    {
        $user = $request->user();
        abort_unless($user && $user->role === 'Admin', 403);

        $status = $request->get('status');
        $from = $request->get('from');
        $to = $request->get('to');

        $query = Payment::with(['user:id,name,email', 'processedBy:id,name'])
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($from, function ($q) use ($from) {
                $q->whereDate('paid_at', '>=', $from);
            })
            ->when($to, function ($q) use ($to) {
                $q->whereDate('paid_at', '<=', $to);
            })
            ->orderBy('paid_at', 'desc');

        $filename = 'pagamenti_' . now()->format('Y-m-d_H-i-s') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'ID',
                'Allievo',
                'Email',
                'Importo',
                'Tipo',
                'Stato',
                'Data pagamento',
                'Scadenza',
                'Anno ricevuta',
                'Operatore',
            ]);

            $query->chunk(200, function ($chunk) use ($handle) {
                foreach ($chunk as $payment) {
                    fputcsv($handle, [
                        $payment->id,
                        $payment->user?->name,
                        $payment->user?->email,
                        number_format((float) $payment->amount, 2, ',', '.'),
                        $payment->type,
                        $payment->status,
                        optional($payment->paid_at)->format('d/m/Y'),
                        optional($payment->due_date)->format('d/m/Y'),
                        $payment->receipt_year,
                        $payment->processedBy?->name,
                    ]);
                }
            });
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
