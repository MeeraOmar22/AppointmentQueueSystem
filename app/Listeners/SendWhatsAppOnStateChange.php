<?php

namespace App\Listeners;

use App\Events\AppointmentStateChanged;
use App\Services\WhatsAppSender;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * WhatsApp Notification Listener
 * 
 * Automatically sends WhatsApp messages when appointment status changes.
 * 
 * - CONFIRMED: "Your appointment is confirmed"
 * - CHECKED_IN: "You have checked in"
 * - IN_TREATMENT: "Treatment starting with Dr. X"
 * - COMPLETED: "Treatment completed, feedback requested"
 * - CANCELLED: "Your appointment has been cancelled"
 */
class SendWhatsAppOnStateChange implements ShouldQueue
{
    use InteractsWithQueue;

    public ?string $queue = 'whatsapp';

    public function __construct(
        private WhatsAppSender $whatsApp,
    ) {}

    public function handle(AppointmentStateChanged $event): void
    {
        $appointment = $event->appointment;
        $newStatus = $event->newStatus;

        try {
            // Match on lowercase status values (as stored in database and returned by enum->value)
            match ($newStatus) {
                'confirmed' => $this->sendConfirmationMessage($appointment),
                'checked_in' => $this->sendCheckInMessage($appointment),
                'in_treatment' => $this->sendTreatmentStartMessage($appointment),
                'completed' => $this->sendCompletionMessage($appointment),
                'cancelled' => $this->sendCancellationMessage($appointment),
                'no_show' => $this->sendNoShowMessage($appointment),
                default => null,
            };
        } catch (\Exception $e) {
            logger()->error('WhatsApp notification failed', [
                'appointment_id' => $appointment->id,
                'status' => $newStatus,
                'error' => $e->getMessage(),
            ]);
            // Don't throw - allow appointment state change even if WhatsApp fails
        }
    }

    private function sendConfirmationMessage($appointment): void
    {
        $phone = $appointment->patient_phone;
        $message = "Hi {$appointment->patient_name}, your appointment is confirmed!\n"
            . "Date: {$appointment->appointment_date}\n"
            . "Time: {$appointment->appointment_time}\n"
            . "Please arrive 5 minutes early.";

        $this->whatsApp->sendMessage($phone, $message);
    }

    private function sendCheckInMessage($appointment): void
    {
        $phone = $appointment->patient_phone;
        $queueNumber = $appointment->queue?->queue_number;
        $message = "Hi {$appointment->patient_name}, you have checked in!\n"
            . ($queueNumber ? "Queue Number: A-" . str_pad($queueNumber, 2, '0', STR_PAD_LEFT) . "\n" : "")
            . "Please wait for your turn.";

        $this->whatsApp->sendMessage($phone, $message);
    }

    private function sendTreatmentStartMessage($appointment): void
    {
        $phone = $appointment->patient_phone;
        $dentist = $appointment->dentist?->name ?? 'Doctor';
        $room = $appointment->queue?->treatment_room_id 
            ? optional(app('App\Models\Room')::find($appointment->queue->treatment_room_id))->room_number 
            : 'TBD';

        $message = "Hi {$appointment->patient_name}, your treatment is starting now!\n"
            . "Doctor: {$dentist}\n"
            . "Room: {$room}\n"
            . "Please proceed to the treatment room.";

        $this->whatsApp->sendMessage($phone, $message);
    }

    private function sendCompletionMessage($appointment): void
    {
        $phone = $appointment->patient_phone;
        $message = "Hi {$appointment->patient_name}, your treatment is complete!\n"
            . "Thank you for visiting us. Please share your feedback.";

        $this->whatsApp->sendMessage($phone, $message);
    }

    private function sendCancellationMessage($appointment): void
    {
        $phone = $appointment->patient_phone;
        $message = "Hi {$appointment->patient_name}, your appointment on {$appointment->appointment_date} has been cancelled.\n"
            . "Please contact us to reschedule.";

        $this->whatsApp->sendMessage($phone, $message);
    }

    private function sendNoShowMessage($appointment): void
    {
        $phone = $appointment->patient_phone;
        $message = "Hi {$appointment->patient_name}, you didn't show up for your appointment on {$appointment->appointment_date}.\n"
            . "Please contact us to reschedule.";

        $this->whatsApp->sendMessage($phone, $message);
    }
}
