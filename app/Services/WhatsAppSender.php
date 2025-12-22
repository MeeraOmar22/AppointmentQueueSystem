<?php

namespace App\Services;

use App\Models\Appointment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WhatsAppSender
{
    public function sendAppointmentConfirmation(Appointment $appointment): void
    {
        $token = config('services.whatsapp.token');
        $phoneId = config('services.whatsapp.phone_id');

        if (!$token || !$phoneId) {
            // WhatsApp Cloud API not configured; silently skip.
            return;
        }

        $to = $this->formatMsisdn($appointment->patient_phone);
        if (!$to) {
            return;
        }

        $trackUrl = url('/visit/' . $appointment->visit_token);
        $dateStr = optional($appointment->appointment_date)->format('d M Y');
        $timeStr = Str::of($appointment->appointment_time)->substr(0, 5);
        $name = $appointment->patient_name;

        $message = "🦷 Dental Clinic Appointment Confirmed\n\n" .
            "Hi {$name},\n" .
            "Your appointment is confirmed for {$dateStr}, {$timeStr}.\n\n" .
            "👉 Track your visit & queue here:\n{$trackUrl}\n\n" .
            "Please tap the link when you arrive at the clinic.";

        // Send via WhatsApp Cloud API (simple text message)
        $endpoint = "https://graph.facebook.com/v17.0/{$phoneId}/messages";
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => ['body' => $message],
        ];

        try {
            Http::withToken($token)->post($endpoint, $payload);
        } catch (\Throwable $e) {
            // Avoid breaking booking flow; logging can be added if needed.
        }
    }

    private function formatMsisdn(?string $raw): ?string
    {
        if (!$raw) return null;
        $digits = preg_replace('/[^0-9]/', '', $raw);
        // If already starts with country code (e.g., 60...), assume E.164 without plus and prepend '+'
        if (Str::startsWith($digits, '60')) {
            return '+' . $digits;
        }
        // If starts with leading 0 (Malaysian local), convert to E.164 (+60...)
        if (Str::startsWith($digits, '0')) {
            return '+60' . substr($digits, 1);
        }
        // Fallback: prepend '+' if not present
        return '+' . $digits;
    }
}
