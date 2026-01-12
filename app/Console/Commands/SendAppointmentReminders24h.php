<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\WhatsAppSender;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAppointmentReminders24h extends Command
{
    protected $signature = 'appointments:send-reminders-24h';
    protected $description = 'Send 24-hour WhatsApp reminders to patients with appointments tomorrow';

    public function handle(WhatsAppSender $whatsAppSender): int
    {
        $tomorrow = Carbon::tomorrow();

        // Get all appointments for tomorrow
        $appointments = Appointment::whereDate('appointment_date', $tomorrow)
            ->where('status', '!=', 'cancelled')
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('No appointments found for tomorrow.');
            return self::SUCCESS;
        }

        $successCount = 0;
        foreach ($appointments as $appointment) {
            try {
                $whatsAppSender->sendAppointmentReminder24h($appointment);
                $successCount++;
                $this->info("24h reminder sent to {$appointment->patient_name} ({$appointment->patient_phone})");
            } catch (\Throwable $e) {
                $this->error("Failed to send 24h reminder to {$appointment->patient_name}: {$e->getMessage()}");
            }
        }

        $this->info("Successfully sent {$successCount} 24h reminder(s) out of {$appointments->count()}.");
        return self::SUCCESS;
    }
}
