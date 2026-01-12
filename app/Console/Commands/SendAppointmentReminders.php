<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\WhatsAppSender;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';
    protected $description = 'Send WhatsApp reminders to patients with appointments today';

    public function handle(WhatsAppSender $whatsAppSender): int
    {
        $today = Carbon::today();

        // Get all appointments for today
        $appointments = Appointment::whereDate('appointment_date', $today)
            ->where('status', '!=', 'cancelled')
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('No appointments found for today.');
            return self::SUCCESS;
        }

        $successCount = 0;
        foreach ($appointments as $appointment) {
            try {
                $whatsAppSender->sendAppointmentReminderToday($appointment);
                $successCount++;
                $this->info("Reminder sent to {$appointment->patient_name} ({$appointment->patient_phone})");
            } catch (\Throwable $e) {
                $this->error("Failed to send reminder to {$appointment->patient_name}: {$e->getMessage()}");
            }
        }

        $this->info("Successfully sent {$successCount} reminder(s) out of {$appointments->count()}.");
        return self::SUCCESS;
    }
}
