<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\MembershipManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AdminMembershipController extends Controller
{
    public function generate(Request $request, MembershipManager $manager): RedirectResponse
    {
        abort_unless($request->user()?->role === 'Admin', 403);

        $clients = User::where('role', 'Client')->get();
        $created = 0;

        foreach ($clients as $client) {
            $membership = $manager->ensureCurrentMembership($client, false);
            $payment = $membership->payment;

            if (!$payment) {
                $membership = $manager->ensureCurrentMembership($client, true);
                $payment = $membership->payment;
                if ($payment && $payment->status === 'pending') {
                    $created++;
                }
                continue;
            }

            if ($payment->status === 'pending') {
                // Assicuriamo che importo e scadenza siano aggiornati all'ultima configurazione
                $manager->ensureCurrentMembership($client, true);
            }
        }

        return redirect()
            ->route('dashboard')
            ->with('status', "Quote verificate. Nuove pendenze create: {$created}");
    }
}
