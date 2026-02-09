<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WhatsAppSender
{
    /**
     * Send appointment confirmation (without tracking link on booking day)
     */
    public function sendAppointmentConfirmation(Appointment $appointment): void
    {
        $token = config('services.whatsapp.token');
        $phoneId = config('services.whatsapp.phone_id');

        if (!$token || !$phoneId) {
            return;
        }

        $to = $this->formatMsisdn($appointment->patient_phone);
        if (!$to) {
            return;
        }

        $dateStr = optional($appointment->appointment_date)->format('d M Y');
        $timeStr = Str::of($appointment->appointment_time)->substr(0, 5);
        $name = $appointment->patient_name;

        // Only include tracking link if appointment is TODAY
        $includeTrackingLink = $appointment->appointment_date && 
                              $appointment->appointment_date->isToday();

        $message = "🦷 Dental Clinic Appointment Confirmed\n\n" .
            "Hi {$name},\n" .
            "Your appointment is confirmed for {$dateStr}, {$timeStr}.\n\n";

        if ($includeTrackingLink) {
            $trackUrl = url('/track/' . $appointment->visit_code);
            $message .= "👉 Track your visit & queue here:\n{$trackUrl}\n\n" .
                       "Please tap the link when you arrive at the clinic.";
        } else {
            $message .= "Please arrive 5-10 minutes early.\n\n" .
                       "We'll send you a tracking link on the day of your appointment.";
        }

        $this->sendMessage($to, $message, $phoneId, $token);
    }

    /**
     * Send reminder with tracking link to patients with appointments today
     */
    public function sendAppointmentReminderToday(Appointment $appointment): void
    {
        $token = config('services.whatsapp.token');
        $phoneId = config('services.whatsapp.phone_id');

        if (!$token || !$phoneId) {
            return;
        }

        $to = $this->formatMsisdn($appointment->patient_phone);
        if (!$to) {
            return;
        }

        $timeStr = Str::of($appointment->appointment_time)->substr(0, 5);
        $name = $appointment->patient_name;
        $trackUrl = url('/track/' . $appointment->visit_code);

        $message = "🦷 Appointment Today!\n\n" .
            "Hi {$name},\n" .
            "Your appointment is at {$timeStr} today.\n\n" .
            "👉 Track your visit & queue:\n{$trackUrl}\n\n" .
            "Tap the link when you're ready. See you soon! 😊";

        $this->sendMessage($to, $message, $phoneId, $token);
    }

    /**
     * Send appointment reminder 24 hours before
     */
    public function sendAppointmentReminder24h(Appointment $appointment): void
    {
        $token = config('services.whatsapp.token');
        $phoneId = config('services.whatsapp.phone_id');

        if (!$token || !$phoneId) {
            return;
        }

        $to = $this->formatMsisdn($appointment->patient_phone);
        if (!$to) {
            return;
        }

        $dateStr = optional($appointment->appointment_date)->format('d M Y');
        $timeStr = Str::of($appointment->appointment_time)->substr(0, 5);
        $name = $appointment->patient_name;

        $message = "🦷 Appointment Reminder\n\n" .
            "Hi {$name},\n" .
            "Reminder: Your appointment is tomorrow ({$dateStr}) at {$timeStr}.\n\n" .
            "Please arrive 5-10 minutes early. See you then! 👋";

        $this->sendMessage($to, $message, $phoneId, $token);
    }

    /**
     * Send custom message to patient
     */
    public function sendCustomMessage(string $phoneNumber, string $message): bool
    {
        $token = config('services.whatsapp.token');
        $phoneId = config('services.whatsapp.phone_id');

        if (!$token || !$phoneId) {
            return false;
        }

        $to = $this->formatMsisdn($phoneNumber);
        if (!$to) {
            return false;
        }

        return $this->sendMessage($to, $message, $phoneId, $token);
    }

    /**
     * Send appointment cancellation notification
     */
    public function sendAppointmentCancellation(Appointment $appointment, ?string $reason = null): void
    {
        $token = config('services.whatsapp.token');
        $phoneId = config('services.whatsapp.phone_id');

        if (!$token || !$phoneId) {
            return;
        }

        $to = $this->formatMsisdn($appointment->patient_phone);
        if (!$to) {
            return;
        }

        $dateStr = optional($appointment->appointment_date)->format('d M Y');
        $timeStr = Str::of($appointment->appointment_time)->substr(0, 5);
        $name = $appointment->patient_name;

        $message = "🦷 Appointment Cancelled\n\n" .
            "Hi {$name},\n" .
            "Your appointment scheduled for {$dateStr} at {$timeStr} has been cancelled.\n\n";

        if ($reason) {
            $message .= "Reason: {$reason}\n\n";
        }

        $message .= "📞 If you have any questions, please contact us:\n" .
            "Phone: 06-677 1940\n\n" .
            "We look forward to serving you again soon! 😊";

        $this->sendMessage($to, $message, $phoneId, $token);
    }

    /**
     * Notify the patient that their queue number was called
     */
    public function sendQueueCallNotification(Appointment $appointment, Queue $queue): void
    {
        $token = config('services.whatsapp.token');
        $phoneId = config('services.whatsapp.phone_id');

        if (!$token || !$phoneId) {
            return;
        }

        $to = $this->formatMsisdn($appointment->patient_phone);
        if (!$to) {
            return;
        }

        $queueLabel = $queue->queue_number ? 'A-' . str_pad($queue->queue_number, 2, '0', STR_PAD_LEFT) : '—';
        $roomLabel = $queue->room?->room_number ?? 'Room assigned shortly';
        $dentistLabel = $queue->dentist?->name ?? 'next available dentist';
        $trackUrl = url('/track/' . $appointment->visit_code);
        $name = $appointment->patient_name;

        $message = "🦷 You're next in line!\n\n" .
            "Hi {$name},\n" .
            "Queue {$queueLabel} is now being called.\n" .
            "Room: {$roomLabel}\n" .
            "Dentist: {$dentistLabel}\n\n" .
            "Track your position: {$trackUrl}\n\n" .
            "Please proceed to the reception desk so we can welcome you. 😊";

        $this->sendMessage($to, $message, $phoneId, $token);
    }

    /**
     * Send feedback link 1 hour after treatment completion
     */
    public function sendFeedbackLink(Appointment $appointment): void
    {
        $token = config('services.whatsapp.token');
        $phoneId = config('services.whatsapp.phone_id');

        if (!$token || !$phoneId) {
            return;
        }

        $to = $this->formatMsisdn($appointment->patient_phone);
        if (!$to) {
            return;
        }

        $name = $appointment->patient_name;
        // Use visit_code for consistency with new track page
        $feedbackUrl = url('/feedback?code=' . urlencode($appointment->visit_code));

        $message = "🦷 Thank You for Your Visit!\n\n" .
            "Hi {$name},\n" .
            "Thank you for choosing Helmy Dental Clinic for your dental care.\n\n" .
            "⭐ We'd love to hear your feedback!\n" .
            "Please share your experience with us:\n\n" .
            "{$feedbackUrl}\n\n" .
            "Your feedback helps us improve our services. Thank you! 😊";

        $this->sendMessage($to, $message, $phoneId, $token);
    }

    /**
     * Core message sending method
     */
    private function sendMessage(string $to, string $body, string $phoneId, string $token): bool
    {
        try {
            $endpoint = "https://graph.facebook.com/v17.0/{$phoneId}/messages";
            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => ['body' => $body],
            ];

            $response = Http::withToken($token)->post($endpoint, $payload);
            
            if (!$response->successful()) {
                $errorBody = $response->json() ?? ['error' => 'Unknown error'];
                \Log::error('WhatsApp API error', [
                    'status' => $response->status(),
                    'phone_id' => $phoneId,
                    'to' => $to,
                    'error_details' => $errorBody,
                    'message_length' => strlen($body)
                ]);
                return false;
            }
            
            return true;
        } catch (\Throwable $e) {
            \Log::error('WhatsApp connection failed', [
                'exception' => get_class($e),
                'error' => $e->getMessage(),
                'to' => $to
            ]);
            return false;
        }
    }

    /**
     * Send treatment start message with room location (when appointment transitions to IN_TREATMENT)
     */
    public function sendTreatmentStartMessage(
        string $phone,
        string $patientName,
        ?string $queueNumber,
        ?string $room
    ): void {
        $token = config('services.whatsapp.token');
        $phoneId = config('services.whatsapp.phone_id');

        if (!$token || !$phoneId) {
            return;
        }

        $to = $this->formatMsisdn($phone);
        if (!$to) {
            return;
        }

        $message = "🦷 Your Turn! Please Proceed\n\n";
        $message .= "Hi {$patientName},\n";
        $message .= "You're now being served.\n\n";

        if ($queueNumber) {
            $message .= "Queue #: {$queueNumber}\n";
        }

        if ($room) {
            $message .= "Room: {$room}\n";
        }

        $message .= "\nPlease proceed to the designated room immediately.\n" .
                   "Thank you! 😊";

        // Note: $to is already formatted by formatMsisdn(), so call sendMessage directly
        $this->sendMessage($to, $message, $phoneId, $token);
    }

    /**
     * Send rescheduling confirmation to patient
     */
    public function sendRescheduleConfirmation(Appointment $appointment): void
    {
        $token = config('services.whatsapp.token');
        $phoneId = config('services.whatsapp.phone_id');

        if (!$token || !$phoneId) {
            return;
        }

        $to = $this->formatMsisdn($appointment->patient_phone);
        if (!$to) {
            return;
        }

        $dateStr = optional($appointment->appointment_date)->format('d M Y');
        $timeStr = Str::of($appointment->appointment_time)->substr(0, 5);
        $name = $appointment->patient_name;
        $trackUrl = url('/track/' . $appointment->visit_code);

        $message = "🦷 Appointment Rescheduled\n\n" .
            "Hi {$name},\n" .
            "Your appointment has been successfully rescheduled.\n\n" .
            "📅 New Date & Time: {$dateStr}, {$timeStr}\n" .
            "🏥 Clinic: " . ucfirst($appointment->clinic_location) . "\n\n" .
            "👉 Track your appointment:\n{$trackUrl}\n\n" .
            "Please arrive 5-10 minutes early. See you soon! 😊";

        $this->sendMessage($to, $message, $phoneId, $token);
    }

    private function formatMsisdn(?string $raw): ?string
    {
        if (!$raw) return null;
        $digits = preg_replace('/[^0-9]/', '', $raw);
        
        if (Str::startsWith($digits, '60')) {
            return '+' . $digits;
        }
        if (Str::startsWith($digits, '0')) {
            return '+60' . substr($digits, 1);
        }
        return '+' . $digits;
    }
}
