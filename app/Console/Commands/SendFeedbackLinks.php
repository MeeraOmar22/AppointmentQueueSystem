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
    protected $description = 'Send feedback links to patients who completed treatment 1 hour ago';

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
        // Get appointments completed exactly 1 hour ago (within 5 minute window)
        $oneHourAgo = Carbon::now()->subHour();
        $fiveMinutesAgo = Carbon::now()->subMinutes(5);

        $completedAppointments = Appointment::where('status', 'completed')
            ->whereBetween('updated_at', [$oneHourAgo, $fiveMinutesAgo])
            ->whereDoesntHave('feedback') // Only if no feedback submitted yet
            ->get();

        $whatsAppSender = new WhatsAppSender();
        $sent = 0;

        foreach ($completedAppointments as $appointment) {
            try {
                $whatsAppSender->sendFeedbackLink($appointment);
                $sent++;
                
                $this->info("Feedback link sent to {$appointment->patient_name} ({$appointment->patient_phone})");
            } catch (\Exception $e) {
                $this->error("Failed to send feedback link to {$appointment->patient_name}: " . $e->getMessage());
            }
        }

        $this->info("Feedback links sent successfully to {$sent} patients");

        return 0;
    }
}
