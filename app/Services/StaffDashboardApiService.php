<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Queue;
use App\Models\Room;
use App\Services\RoomAssignmentService;
use App\Services\WhatsAppSender;
use App\Services\ActivityLogger;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Staff Dashboard API Service
 * 
 * Centralized service for all staff dashboard operations.
 * Ensures all state transitions go through AppointmentStateService.
 * Handles validation, error handling, and consistent responses.
 */
class StaffDashboardApiService
{
    public function __construct(
        private AppointmentStateService $stateService,
        private RoomAssignmentService $roomService,
        private WhatsAppSender $whatsApp,
        private ActivityLogger $activityLogger,
    ) {}

    /**
     * Transition appointment to a new status
     * 
     * This is the ONLY way staff should change appointment status.
     * All business logic (queue creation, room assignment, WhatsApp) 
     * is triggered automatically by the state machine.
     * 
     * @param Appointment $appointment
     * @param string $targetStatus
     * @param string $reason
     * @return array
     * @throws InvalidStateTransition
     */
    public function transitionAppointmentStatus(
        Appointment $appointment,
        string $targetStatus,
        string $reason = 'Status changed via staff dashboard'
    ): array {
        $previousStatus = $appointment->status->value; // Get the enum value

        try {
            // Use state machine for transition
            $result = $this->stateService->transitionTo(
                $appointment,
                $targetStatus,
                $reason
            );

            // Check if transition was successful
            if (!$result) {
                return [
                    'success' => false,
                    'error' => "Invalid transition from {$previousStatus} to {$targetStatus}",
                    'previousStatus' => $previousStatus,
                ];
            }

            // Refresh to get updated status
            $appointment->refresh();
            
            // Also load/reload the queue relationship since it may have been created during automation
            $appointment->load('queue');

            return [
                'success' => true,
                'appointment' => [
                    'id' => $appointment->id,
                    'status' => $appointment->status->value,
                    'previousStatus' => $previousStatus,
                    'patientName' => $appointment->patient_name,
                    'queueNumber' => $appointment->queue?->queue_number,
                    'room' => $appointment->queue?->treatment_room_id 
                        ? Room::find($appointment->queue->treatment_room_id)?->room_number 
                        : null,
                ],
                'event' => [
                    'type' => 'appointment.status_changed',
                    'timestamp' => now()->toIso8601String(),
                ],
            ];
        } catch (InvalidStateTransition $e) {
            throw new InvalidStateTransitionException(
                "Cannot transition from {$previousStatus} to {$targetStatus}: " . $e->getMessage(),
                422
            );
        } catch (Exception $e) {
            logger()->error('Staff appointment transition failed', [
                'appointment_id' => $appointment->id,
                'from_status' => $previousStatus,
                'to_status' => $targetStatus,
                'reason' => $reason,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get today's appointments with proper sorting and filtering
     * HIGH-001 FIX: Filtered by clinic location for multi-clinic accuracy
     * 
     * @return array
     */
    public function getTodayAppointments(): array
    {
        $today = now()->toDateString();
        
        // HIGH-001 FIX: Get clinic location from authenticated staff member
        $clinicLocation = auth()->user()?->clinic_location ?? 'seremban';

        // FIX: Load all relationships with NO select() restrictions
        // Removed column selection to ensure foreign keys are loaded for relationship hydration
        // Note: Room is accessed through queue->room, not directly from appointment
        $appointments = Appointment::with(['service', 'dentist', 'queue.room'])
            ->whereDate('appointment_date', $today)
            ->where('clinic_location', $clinicLocation)  // ← Filter by clinic
            ->orderBy('appointment_time')
            ->get()
            ->map(function ($appointment) {
                return $this->formatAppointmentResponse($appointment);
            })
            ->sortBy(function ($apt) {
                // Sort by queue number (nulls last)
                return $apt['queueNumber'] === '—' ? PHP_INT_MAX : $apt['queueNumber'];
            })
            ->values()
            ->all();

        return [
            'success' => true,
            'data' => [
                'appointments' => $appointments,
                'total' => count($appointments),
            ],
            'meta' => [
                'timestamp' => now()->toIso8601String(),
                'date' => $today,
            ],
        ];
    }

    /**
     * Get summary statistics for today
     * 
     * Statistics are derived ONLY from appointment.status, never from queue records.
     * HIGH-001 FIX: Now filtered by clinic location for multi-clinic accuracy
     * Previously: Returned mixed stats from all clinics
     * Now: Only returns stats for the staff member's assigned clinic
     * 
     * @return array
     */
    public function getSummaryStatistics(): array
    {
        try {
            $today = now()->toDateString();
            
            // Try to get clinic location from authenticated staff member
            // Fall back to 'seremban' if auth fails
            $clinicLocation = 'seremban';
            if (auth()->check() && auth()->user()) {
                $clinicLocation = auth()->user()->clinic_location ?? 'seremban';
            }

            $appointments = Appointment::whereDate('appointment_date', $today)
                ->where('clinic_location', $clinicLocation)  // ← CRITICAL: Filter by clinic
                ->get();

            $stats = [
                'total' => $appointments->count(),
                'booked' => $appointments->filter(fn($apt) => $apt->status->value === 'booked')->count(),
                'confirmed' => $appointments->filter(fn($apt) => $apt->status->value === 'confirmed')->count(),
                'checkedIn' => $appointments->filter(fn($apt) => $apt->status->value === 'checked_in')->count(),
                'waiting' => $appointments->filter(fn($apt) => $apt->status->value === 'waiting')->count(),
                'inTreatment' => $appointments->filter(fn($apt) => $apt->status->value === 'in_treatment')->count(),
                'completed' => $appointments->filter(fn($apt) => $apt->status->value === 'completed')->count(),
                'cancelled' => $appointments->filter(fn($apt) => $apt->status->value === 'cancelled')->count(),
                'noShow' => $appointments->filter(fn($apt) => $apt->status->value === 'no_show')->count(),
                // Frontend keys for dashboard display
                'queued' => $appointments->filter(fn($apt) => !empty($apt->queue))->count(),
                'in_service' => $appointments->filter(fn($apt) => $apt->status->value === 'in_treatment')->count(),
            ];

            // Active queue (excluding completed, cancelled, no-show, feedback-sent)
            $activeStatuses = ['checked_in', 'waiting', 'in_treatment'];
            $stats['activeInQueue'] = $appointments
                ->filter(fn($apt) => in_array($apt->status->value, $activeStatuses))
                ->count();

            return [
                'success' => true,
                'data' => $stats,
                'meta' => [
                    'timestamp' => now()->toIso8601String(),
                    'date' => $today,
                ],
            ];
        } catch (\Exception $e) {
            \Log::error('getSummaryStatistics error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return empty stats on error
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => [
                    'total' => 0,
                    'booked' => 0,
                    'confirmed' => 0,
                    'checkedIn' => 0,
                    'waiting' => 0,
                    'inTreatment' => 0,
                    'completed' => 0,
                    'cancelled' => 0,
                    'noShow' => 0,
                    'activeInQueue' => 0,
                ],
            ];
        }
    }

    /**
     * Get single appointment details
     * 
     * @param int $appointmentId
     * @return array
     * @throws ModelNotFoundException
     */
    public function getAppointmentDetails(int $appointmentId): array
    {
        $appointment = Appointment::with(['service', 'queue', 'dentist', 'feedback'])
            ->findOrFail($appointmentId);

        return [
            'success' => true,
            'data' => [
                'appointment' => $this->formatAppointmentResponse($appointment),
                'details' => [
                    'patientEmail' => $appointment->patient_email,
                    'patientPhone' => $appointment->patient_phone,
                    'clinicLocation' => $appointment->clinic_location,
                    'appointmentDate' => $appointment->appointment_date,
                    'appointmentTime' => $appointment->appointment_time,
                    'service' => $appointment->service ? [
                        'id' => $appointment->service->id,
                        'name' => $appointment->service->name,
                        'duration' => $appointment->service->estimated_duration,
                    ] : null,
                    'dentist' => $appointment->dentist ? [
                        'id' => $appointment->dentist->id,
                        'name' => $appointment->dentist->name,
                    ] : null,
                    'queue' => $appointment->queue ? [
                        'id' => $appointment->queue->id,
                        'queueNumber' => $appointment->queue->queue_number,
                        'queueStatus' => $appointment->queue->queue_status,
                        'checkedInAt' => $appointment->queue->check_in_time,
                    ] : null,
                    'createdAt' => $appointment->created_at,
                    'updatedAt' => $appointment->updated_at,
                ],
            ],
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ];
    }

    /**
     * Get active queue (waiting + in_treatment only)
     * 
     * Excludes: completed, cancelled, no_show, feedback_sent, booked, confirmed
     * 
     * CRITICAL: Eagerly loads dentist and room relationships to prevent "Unassigned" displays
     * 
     * @return array
     */
    public function getActiveQueue(): array
    {
        $today = now()->toDateString();
        $clinicLocation = config('clinic.location', 'seremban');

        // FIX #7: Add clinic_location filter to prevent cross-clinic data leaks
        // FIX: Load all relationships with NO select() restrictions
        // Ensures dentist_id and room_id foreign keys are available for relationship hydration
        // Note: Room is accessed through queue->room, not directly from appointment
        $queueAppointments = Appointment::with(['service', 'dentist', 'queue.room'])
            ->whereDate('appointment_date', $today)
            ->where('clinic_location', $clinicLocation)  // ← FIX: Add location filter
            ->whereIn('status', ['checked_in', 'waiting', 'in_treatment'])
            ->get()
            ->sortBy(function ($apt) {
                // Sort by queue check-in time (deterministic)
                return $apt->queue?->check_in_time ?? now()->addYear();
            })
            ->values();

        $queue = $queueAppointments->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'queueId' => $appointment->queue?->id,
                'queueNumber' => $appointment->queue?->queue_number ? 
                    'A-' . str_pad($appointment->queue->queue_number, 2, '0', STR_PAD_LEFT) : 
                    '—',
                'patientName' => $appointment->patient_name,
                'patientPhone' => $appointment->patient_phone,
                'status' => $appointment->status,
                'service' => $appointment->service?->name,
                'dentist' => $appointment->dentist?->name ?? 'Unassigned',
                'checkedInAt' => $appointment->queue?->check_in_time,
            ];
        });

        return [
            'success' => true,
            'data' => [
                'queue' => $queue->all(),
                'total' => $queue->count(),
                'currentServing' => $this->getCurrentServingPatient(),
            ],
            'meta' => [
                'timestamp' => now()->toIso8601String(),
                'date' => $today,
            ],
        ];
    }

    /**
     * Get currently serving patient (IN_TREATMENT status only)
     * 
     * CRITICAL: Eagerly loads dentist and room relationships
     * 
     * @return array|null
     */
    private function getCurrentServingPatient(): ?array
    {
        $today = now()->toDateString();
        
        // FIX: Load all relationships with NO select() restrictions
        // Ensures relationships hydrate correctly for display in staff dashboard
        // Note: Room is accessed through queue->room, not directly from appointment
        $appointment = Appointment::with(['service', 'dentist', 'queue.room'])
            ->whereDate('appointment_date', $today)
            ->where('status', 'in_treatment')
            ->first();

        if (!$appointment) {
            return null;
        }

        return [
            'id' => $appointment->id,
            'queueNumber' => $appointment->queue?->queue_number ? 
                'A-' . str_pad($appointment->queue->queue_number, 2, '0', STR_PAD_LEFT) : 
                '—',
            'patientName' => $appointment->patient_name,
            'service' => $appointment->service?->name,
            'dentist' => $appointment->dentist?->name,
            // FIX: Use eager-loaded relationship instead of fallback Room::find()
            'room' => $appointment->queue?->room?->room_number,
        ];
    }

    /**
     * Format appointment for API response
     * 
     * Consistent structure for all endpoints.
     * 
     * @param Appointment $appointment
     * @return array
     */
    private function formatAppointmentResponse(Appointment $appointment): array
    {
        return [
            'id' => $appointment->id,
            'visitCode' => $appointment->visit_code ?? 'APT-' . $appointment->id,
            'patientName' => $appointment->patient_name,
            'status' => $appointment->status->value,  // Get string value from enum
            'service' => $appointment->service?->name ?? '—',
            'dentist' => $appointment->dentist?->name ?? 'Unassigned',
            'appointmentTime' => $appointment->appointment_time,
            'queueNumber' => $appointment->queue?->queue_number ? 
                'A-' . str_pad($appointment->queue->queue_number, 2, '0', STR_PAD_LEFT) : 
                '—',
            'room' => $appointment->queue?->room_id 
                ? Room::find($appointment->queue->room_id)?->room_number 
                : null,
            'checkedInAt' => $appointment->queue?->check_in_time,
        ];
    }
}

// Exception classes
class UnauthorizedException extends Exception {}
class InvalidStateTransitionException extends Exception {}
