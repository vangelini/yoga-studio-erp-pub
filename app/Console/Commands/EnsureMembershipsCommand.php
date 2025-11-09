<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\MembershipManager;
use Illuminate\Console\Command;

class EnsureMembershipsCommand extends Command
{
    protected $signature = 'membership:ensure {--refresh-pending : ricrea il pagamento solo se non esiste una pendenza attiva}';

    protected $description = 'Garantisce che ogni allieva/o abbia la sottoscrizione annuale e il pagamento in stato pending per la stagione corrente.';

    public function handle(MembershipManager $manager): int
    {
        $refreshOnly = $this->option('refresh-pending');
        $season = $manager->determineCurrentSeason();

        $clients = User::where('role', 'Client')->get();
        $created = 0;

        foreach ($clients as $client) {
            $membership = $manager->ensureCurrentMembership($client, false);
            $payment = $membership->payment;

            $shouldCreate = !$payment;

            if ($refreshOnly && $payment && $payment->status !== 'pending') {
                $shouldCreate = false;
            }

            if ($refreshOnly === false && $payment && $payment->status !== 'pending') {
                // non toccare pagamenti già saldati/annullati
                $shouldCreate = false;
            }

            if ($shouldCreate) {
                $membership = $manager->ensureCurrentMembership($client, true);
                $membership->refresh();
                if ($membership->payment && $membership->payment->status === 'pending') {
                    $created++;
                }
            }
        }

        $this->info("Allievi processati: {$clients->count()} | Pendenze create: {$created}");

        return self::SUCCESS;
    }
}
