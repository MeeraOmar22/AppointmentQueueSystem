<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Assign queue numbers for today's appointments at clinic opening (7:30 AM)
        $schedule->command('queue:assign-today')->dailyAt('07:30');

        // Send WhatsApp reminders to today's appointments at 7:45 AM
        $schedule->command('appointments:send-reminders')->dailyAt('07:45');

        // Send 24-hour reminders at 10:00 AM for appointments tomorrow
        $schedule->command('appointments:send-reminders-24h')->dailyAt('10:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}
