<?php

namespace App\Services;

use App\Models\Appointment;
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
            $trackUrl = url('/visit/' . $appointment->visit_token);
            $message .= "👉 Track your visit & queue here:\n{$trackUrl}\n\n" .
                       "Please tap the link when you arrive at the clinic.";
        } else {
            $message .= "Please arrive 5-10 minutes early.\n\n" .
                       "We'll send you a tracking link on the day of your appointment.";
        }

        $this->sendMessage($to, $message, $phoneId, $token);
    }

    /**
     * Send reminder with tracking & check-in links to patients with appointments today
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
        $trackUrl = url('/visit/' . $appointment->visit_token);
        $checkInUrl = url('/checkin?token=' . $appointment->visit_token);

        $message = "🦷 Appointment Today!\n\n" .
            "Hi {$name},\n" .
            "Your appointment is at {$timeStr} today.\n\n" .
            "📍 Track Queue:\n{$trackUrl}\n\n" .
            "✅ Quick Check-In:\n{$checkInUrl}\n\n" .
            "Tap the links when you're ready. See you soon! 😊";

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
        $feedbackUrl = url('/feedback?code=' . $appointment->visit_code);

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
            return $response->successful();
        } catch (\Throwable $e) {
            \Log::error('WhatsApp message failed', ['error' => $e->getMessage()]);
            return false;
        }
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
