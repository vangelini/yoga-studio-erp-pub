<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendOverdueNotificationsCommand extends Command
{
    protected $signature = 'notifications:send-overdue';

    protected $description = 'Invia automaticamente le notifiche di morosità in base ai giorni configurati.';

    public function __construct(private NotificationService $notifications)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->notifications->sendOverdueNotifications();
        $this->info('Notifiche di morosità elaborate.');

        return self::SUCCESS;
    }
}
