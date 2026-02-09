<?php

namespace App\Http\Controllers\Public;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Dentist;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PublicBookingController extends Controller
{
    /**
     * GET /api/public/booking/form-data
     * Return data needed for booking form (services, dentists, etc.)
     */
    public function formData(): JsonResponse
    {
        try {
            $services = Service::where('status', 1)
                ->select('id', 'name', 'price', 'estimated_duration')
                ->orderBy('name')
                ->get()
                ->map(fn($s) => [
                    'id' => $s->id,
                    'name' => $s->name ?? '',
                    'price' => number_format($s->price ?? 0, 2),
                    'duration' => (int)($s->estimated_duration ?? 0),
                ]);

            $dentists = Dentist::where('status', 1)
                ->select('id', 'name')
                ->orderBy('name')
                ->get()
                ->map(fn($d) => [
                    'id' => $d->id,
                    'name' => $d->name ?? '',
                ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'services' => $services,
                    'dentists' => $dentists,
                    'clinics' => [
                        ['value' => 'seremban', 'label' => 'Seremban - No. 25A, Tingkat 1, Lorong Sri Mawar 12/2, 70450 Seremban'],
                        ['value' => 'kuala_pilah', 'label' => 'Kuala Pilah - No. 902, Jalan Raja Melewar, 72000 Kuala Pilah'],
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => true,
                'data' => [
                    'services' => [],
                    'dentists' => [],
                    'clinics' => [],
                ],
            ], 200);
        }
    }

    /**
     * POST /api/public/booking/submit
     * Submit a new appointment booking
     */
    public function submit(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'patient_name' => 'required|string|max:255',
                'patient_phone' => 'required|string|max:20',
                'patient_email' => 'nullable|email',
                'service_id' => 'required|integer|exists:services,id',
                'dentist_id' => 'nullable|integer|exists:dentists,id',
                'appointment_date' => 'required|date|after_or_equal:today',
                'appointment_time' => 'required|date_format:H:i',
                'clinic_location' => 'required|in:seremban,kuala_pilah',
            ]);

            // Create appointment
            $appointment = Appointment::create([
                'patient_name' => $validated['patient_name'],
                'patient_phone' => $validated['patient_phone'],
                'patient_email' => $validated['patient_email'] ?? null,
                'service_id' => $validated['service_id'],
                'dentist_id' => $validated['dentist_id'] ?? null,
                'appointment_date' => $validated['appointment_date'],
                'appointment_time' => $validated['appointment_time'],
                'clinic_location' => $validated['clinic_location'],
                'booking_source' => 'public',
                'status' => 'booked',
                'visit_code' => $this->generateVisitCode(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appointment booked successfully',
                'data' => [
                    'appointment_id' => $appointment->id,
                    'visit_code' => $appointment->visit_code,
                    'patient_name' => $appointment->patient_name,
                    'appointment_date' => $appointment->appointment_date->format('d/m/Y'),
                    'appointment_time' => substr($appointment->appointment_time, 0, 5),
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating appointment',
            ], 500);
        }
    }

    /**
     * POST /api/public/booking/check-in
     * Check in an appointment by visit code
     */
    public function checkIn(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'visit_code' => 'required|string|exists:appointments,visit_code',
            ]);

            $appointment = Appointment::where('visit_code', $validated['visit_code'])->firstOrFail();

            // Update status to checked_in
            $appointment->update(['status' => 'checked_in']);

            // Get or create queue entry
            $queue = Queue::where('appointment_id', $appointment->id)->first();
            $queueNumber = $queue?->queue_number ?? 'N/A';

            // Get queue pause status
            $queueSettings = DB::table('queue_settings')->first();
            $isPaused = $queueSettings?->is_paused ?? false;

            return response()->json([
                'success' => true,
                'message' => 'Checked in successfully',
                'data' => [
                    'appointment_id' => $appointment->id,
                    'status' => 'checked_in',
                    'queue_number' => $queueNumber,
                    'patient_name' => $appointment->patient_name,
                    'queue_paused' => $isPaused,
                    'pause_message' => $isPaused ? "Queue is currently paused. You are #{$queueNumber} in queue. Staff will call you when queue resumes." : null,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid visit code',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking in appointment',
            ], 500);
        }
    }

    /**
     * Generate unique visit code for appointment
     */
    private function generateVisitCode(): string
    {
        do {
            $code = 'V' . strtoupper(bin2hex(random_bytes(4)));
        } while (Appointment::where('visit_code', $code)->exists());

        return $code;
    }
}
