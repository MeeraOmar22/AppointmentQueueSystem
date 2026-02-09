<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\WhatsAppSender;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendFeedbackLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feedback:send-links';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Retry sending feedback links to patients (sent instantly after treatment, but this retries for any that failed)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // This command now retries feedback links for any that are still in FEEDBACK_SCHEDULED state
        // Most feedback links are sent instantly when treatment completes
        // This catches any that failed and retries them
        
        $scheduledAppointments = Appointment::where('status', 'feedback_scheduled')
            ->whereDoesntHave('feedback') // Only if no feedback submitted yet
            ->get();

        $whatsAppSender = new WhatsAppSender();
        $sent = 0;

        foreach ($scheduledAppointments as $appointment) {
            try {
                $whatsAppSender->sendFeedbackLink($appointment);
                $sent++;
                
                $this->info("Feedback link (retry) sent to {$appointment->patient_name} ({$appointment->patient_phone})");
            } catch (\Exception $e) {
                $this->error("Failed to send feedback link to {$appointment->patient_name}: " . $e->getMessage());
            }
        }

        $this->info("Feedback links sent successfully to {$sent} patients (retries)");

        return 0;
    }
}
