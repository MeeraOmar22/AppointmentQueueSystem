<?php

namespace App\Http\Controllers\Public;

use App\Models\Appointment;
use App\Models\Queue;
use Illuminate\Http\JsonResponse;

class PublicTrackingController extends Controller
{
    /**
     * GET /api/public/track/{visit_code}
     * Return appointment status by visit code
     */
    public function track($visitCode): JsonResponse
    {
        try {
            if (!$visitCode) {
                return response()->json([
                    'success' => false,
                    'message' => 'Visit code is required',
                ], 400);
            }

            $appointment = Appointment::where('visit_code', $visitCode)->first();

            if (!$appointment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Appointment not found',
                ], 404);
            }

            $queue = Queue::where('appointment_id', $appointment->id)->first();
            
            // Get raw status to avoid enum casting issues
            $status = $appointment->getRawOriginal('status') ?? 'unknown';

            // Map status to user-friendly message
            $statusLabels = [
                'booked' => 'Appointment Confirmed',
                'confirmed' => 'Appointment Confirmed',
                'checked_in' => 'Checked In',
                'waiting' => 'Waiting for Treatment',
                'in_treatment' => 'Currently in Treatment',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
                'no_show' => 'No Show',
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $status,
                    'status_label' => $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status)),
                    'queue_number' => $queue ? 'A-' . str_pad($queue->queue_number, 2, '0', STR_PAD_LEFT) : null,
                    'patient_name' => $appointment->patient_name ?? 'N/A',
                    'service' => $appointment->service?->name ?? null,
                    'appointment_date' => $appointment->appointment_date?->format('d/m/Y') ?? null,
                    'appointment_time' => $appointment->appointment_time ? substr($appointment->appointment_time, 0, 5) : null,
                    'dentist' => $appointment->dentist?->name ?? ($queue?->dentist?->name ?? null),
                    'room' => $queue?->room ? 'Room ' . $queue->room->room_number : null,
                    'estimated_wait' => $this->estimateWait($queue),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving appointment',
            ], 500);
        }
    }

    /**
     * Estimate wait time based on queue position
     */
    private function estimateWait($queue): ?string
    {
        if (!$queue || !$queue->queue_status) {
            return null;
        }

        if ($queue->queue_status === 'in_treatment') {
            return 'Being served';
        }

        if ($queue->queue_status !== 'waiting') {
            return null;
        }

        // Count patients ahead in queue
        $ahead = Queue::where('queue_status', 'waiting')
            ->where('queue_number', '<', $queue->queue_number)
            ->count();

        if ($ahead === 0) {
            return 'Next';
        }

        // Estimate: ~15 min per patient
        $minutes = $ahead * 15;
        return $minutes . ' min';
    }
}
