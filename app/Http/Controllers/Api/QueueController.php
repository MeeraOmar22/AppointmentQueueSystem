<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Queue;
use App\Models\Room;
use App\Services\CheckInService;
use App\Services\QueueAssignmentService;
use App\Services\LateNoShowService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QueueController extends Controller
{
    /**
     * Check in a patient
     * 
     * POST /api/check-in
     */
    public function checkIn(Request $request, CheckInService $checkInService): JsonResponse
    {
        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'visited_code' => 'nullable|string',
        ]);

        $appointment = Appointment::findOrFail($validated['appointment_id']);

        // Validate check-in eligibility
        $validation = $checkInService->validateCheckIn($appointment);
        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validation['errors'][0],
                'errors' => $validation['errors'],
            ], 400);
        }

        // Check if patient is late
        if ($checkInService->isLate($appointment)) {
            $queue = $checkInService->checkInLate($appointment);
            return response()->json([
                'success' => true,
                'message' => 'Patient checked in as LATE',
                'queue' => $queue->load('appointment', 'room', 'dentist'),
                'status' => 'late',
            ]);
        }

        // Normal check-in
        $queue = $checkInService->checkIn($appointment);

        return response()->json([
            'success' => true,
            'message' => 'Patient checked in successfully',
            'queue' => $queue->load('appointment', 'room', 'dentist'),
            'status' => 'checked_in',
        ]);
    }

    /**
     * Get next patient to be called
     * 
     * GET /api/queue/next?clinic_location=seremban
     */
    public function getNextPatient(Request $request, QueueAssignmentService $assignmentService): JsonResponse
    {
        $clinicLocation = $request->query('clinic_location', 'seremban');

        $queue = $assignmentService->assignNextPatient($clinicLocation);

        if (!$queue) {
            return response()->json([
                'success' => false,
                'message' => 'No waiting patients available',
                'queue' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Next patient assigned',
            'queue' => $queue->load('appointment', 'room', 'dentist'),
        ]);
    }

    /**
     * Get queue status
     * 
     * GET /api/queue/{queue_id}/status
     */
    public function getQueueStatus(Queue $queue): JsonResponse
    {
        $assignmentService = new QueueAssignmentService();
        $waitTime = $assignmentService->getEstimatedWaitTime($queue);

        return response()->json([
            'success' => true,
            'queue_id' => $queue->id,
            'queue_number' => $queue->queue_number,
            'queue_status' => $queue->queue_status,
            'room' => $queue->room?->room_number,
            'dentist' => $queue->dentist?->name,
            'appointment' => $queue->appointment,
            'estimated_wait_time' => $waitTime,
            'estimated_wait_time_label' => $waitTime . ' minutes',
        ]);
    }

    /**
     * Update queue status (staff action)
     * 
     * PATCH /api/queue/{queue_id}/status
     */
    public function updateQueueStatus(Request $request, Queue $queue, QueueAssignmentService $assignmentService): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:called,start_treatment,complete_treatment,mark_completed',
        ]);

        match ($validated['action']) {
            'called' => $queue->markCalled(),
            'start_treatment' => $assignmentService->startTreatment($queue),
            'complete_treatment', 'mark_completed' => $assignmentService->completeTreatment($queue),
        };

        return response()->json([
            'success' => true,
            'message' => 'Queue status updated',
            'queue' => $queue->load('appointment', 'room', 'dentist'),
        ]);
    }

    /**
     * Get room availability
     * 
     * GET /api/rooms/status?clinic_location=seremban
     */
    public function getRoomStatus(Request $request): JsonResponse
    {
        $clinicLocation = $request->query('clinic_location', 'seremban');

        $rooms = Room::where('clinic_location', $clinicLocation)
            ->with('currentPatient.appointment')
            ->orderBy('room_number')
            ->get()
            ->map(function (Room $room) {
                return [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                    'status' => $room->status,
                    'current_patient' => $room->currentPatient ? [
                        'patient_name' => $room->currentPatient->appointment->patient_name,
                        'service' => $room->currentPatient->appointment->service?->name,
                        'dentist' => $room->currentPatient->dentist?->name,
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'clinic_location' => $clinicLocation,
            'rooms' => $rooms,
            'available_rooms' => $rooms->where('status', 'available')->count(),
        ]);
    }

    /**
     * Get queue statistics
     * 
     * GET /api/queue/stats?clinic_location=seremban
     */
    public function getQueueStats(Request $request, QueueAssignmentService $assignmentService): JsonResponse
    {
        $clinicLocation = $request->query('clinic_location', 'seremban');
        $stats = $assignmentService->getQueueStats($clinicLocation);

        return response()->json([
            'success' => true,
            'clinic_location' => $clinicLocation,
            'stats' => $stats,
        ]);
    }

    /**
     * Create walk-in patient
     * 
     * POST /api/walk-in
     */
    public function createWalkIn(Request $request, LateNoShowService $lateNoShowService): JsonResponse
    {
        $validated = $request->validate([
            'patient_name' => 'required|string|max:255',
            'patient_phone' => 'required|string',
            'patient_email' => 'nullable|email',
            'service_id' => 'required|exists:services,id',
            'clinic_location' => 'required|in:seremban,kuala_pilah',
            'dentist_id' => 'nullable|exists:dentists,id',
        ]);

        $appointment = $lateNoShowService->createWalkIn($validated);

        return response()->json([
            'success' => true,
            'message' => 'Walk-in patient created',
            'appointment' => $appointment->load('queue'),
        ], 201);
    }

    /**
     * Handle late check-in automation
     * 
     * POST /api/auto-mark-late
     */
    public function autoMarkLate(Request $request, LateNoShowService $lateNoShowService): JsonResponse
    {
        $validated = $request->validate([
            'threshold_minutes' => 'nullable|integer|min:5|max:120',
        ]);

        $threshold = $validated['threshold_minutes'] ?? 15;
        $marked = $lateNoShowService->markLateAppointments($threshold);

        return response()->json([
            'success' => true,
            'message' => "$marked appointments marked as late",
            'marked_count' => $marked,
        ]);
    }

    /**
     * Handle no-show automation
     * 
     * POST /api/auto-mark-no-show
     */
    public function autoMarkNoShow(Request $request, LateNoShowService $lateNoShowService): JsonResponse
    {
        $validated = $request->validate([
            'threshold_minutes' => 'nullable|integer|min:5|max:120',
        ]);

        $threshold = $validated['threshold_minutes'] ?? 30;
        $marked = $lateNoShowService->markNoShowAppointments($threshold);

        return response()->json([
            'success' => true,
            'message' => "$marked appointments marked as no-show",
            'marked_count' => $marked,
        ]);
    }
}
