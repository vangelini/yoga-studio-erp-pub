<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class RunScheduledNotificationsCommand extends Command
{
    protected $signature = 'notifications:run-scheduled';

    protected $description = 'Esegue l\'invio delle notifiche pianificate.';

    public function __construct(private NotificationService $notifications)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->notifications->runScheduledNotifications();
        $this->info('Notifiche pianificate elaborate.');

        return self::SUCCESS;
    }
}
