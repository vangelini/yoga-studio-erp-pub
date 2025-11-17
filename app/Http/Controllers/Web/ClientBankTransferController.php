<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\View\View;

class ClientBankTransferController extends Controller
{
    public function __invoke(): View
    {
        $this->authorizeClient();

        $message = Setting::query()
            ->where('key', 'bank_transfer_info_message')
            ->value('value');

        $message = $message ?: 'Puoi effettuare il pagamento tramite bonifico bancario. Inserisci i dati richiesti e invia la ricevuta all\'amministrazione.';

        return view('dashboard.bank-transfer', [
            'message' => $message,
        ]);
    }

    private function authorizeClient(): void
    {
        abort_unless(auth()->check() && auth()->user()->role === 'Client', 403);
    }
}
