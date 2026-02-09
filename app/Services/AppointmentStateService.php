<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Queue;
use App\Services\WhatsAppSender;
use App\Services\ActivityLogger;
use App\Events\AppointmentStateChanged;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * Manages patient appointment state transitions and automation
 * 
 * All state changes must go through this service to ensure:
 * - Valid transitions only
 * - Proper automation triggered
 * - Audit trail maintained
 * 
 * Integrates with DentistService to manage dentist availability:
 * - Marks dentist BUSY when treatment starts (IN_TREATMENT)
 * - Marks dentist AVAILABLE when treatment completes (COMPLETED)
 */
class AppointmentStateService
{
    public function __construct(
        private DentistService $dentistService,
    ) {}

    // State definitions
    const STATE_BOOKED = 'booked';
    const STATE_CONFIRMED = 'confirmed';
    const STATE_CANCELLED = 'cancelled';
    const STATE_NO_SHOW = 'no_show';
    const STATE_CHECKED_IN = 'checked_in';
    const STATE_WAITING = 'waiting';
    const STATE_CALLED = 'called';
    const STATE_IN_TREATMENT = 'in_treatment';
    const STATE_COMPLETED = 'completed';
    const STATE_FEEDBACK_SCHEDULED = 'feedback_scheduled';
    const STATE_FEEDBACK_SENT = 'feedback_sent';

    // Terminal states (no further transitions)
    const TERMINAL_STATES = [
        self::STATE_CANCELLED,
        self::STATE_NO_SHOW,
        self::STATE_FEEDBACK_SENT,
    ];

    /**
     * STATE MACHINE DOCUMENTATION
     * 
     * NORMAL FLOW (Happy Path):
     *   BOOKED → CONFIRMED → CHECKED_IN → WAITING → CALLED → IN_TREATMENT → COMPLETED → FEEDBACK_SCHEDULED → FEEDBACK_SENT
     * 
     * EARLY CANCELLATION:
     *   BOOKED → CANCELLED (terminal) OR
     *   CONFIRMED → CANCELLED (terminal) OR
     *   CHECKED_IN → CANCELLED (terminal) OR
     *   WAITING → CANCELLED (terminal) OR
     *   CALLED → CANCELLED (terminal)
     * 
     * NO-SHOW:
     *   CONFIRMED → NO_SHOW (terminal)
     * 
     * TREATMENT RECOVERY:
     *   IN_TREATMENT → WAITING (if something goes wrong during treatment, move patient back to queue)
     *   CALLED → WAITING (if patient not ready when called)
     * 
     * Terminal States: CANCELLED, NO_SHOW, FEEDBACK_SENT
     * (Once in a terminal state, no further transitions are allowed)
     */

    // Allowed state transitions
    const ALLOWED_TRANSITIONS = [
        self::STATE_BOOKED => [self::STATE_CONFIRMED, self::STATE_CHECKED_IN, self::STATE_CANCELLED],
        self::STATE_CONFIRMED => [self::STATE_CHECKED_IN, self::STATE_NO_SHOW, self::STATE_CANCELLED],
        self::STATE_CHECKED_IN => [self::STATE_WAITING, self::STATE_CANCELLED],
        self::STATE_WAITING => [self::STATE_CALLED, self::STATE_IN_TREATMENT, self::STATE_CANCELLED],
        self::STATE_CALLED => [self::STATE_IN_TREATMENT, self::STATE_WAITING, self::STATE_CANCELLED],
        self::STATE_IN_TREATMENT => [self::STATE_COMPLETED, self::STATE_WAITING],
        self::STATE_COMPLETED => [self::STATE_FEEDBACK_SCHEDULED],
        self::STATE_FEEDBACK_SCHEDULED => [self::STATE_FEEDBACK_SENT],
        self::STATE_CANCELLED => [],
        self::STATE_NO_SHOW => [],
        self::STATE_FEEDBACK_SENT => [],
    ];

    /**
     * Transition appointment to new state
     * Validates transition and triggers automation
     * 
     * CRIT-005 FIX: All status changes now atomic with dentist updates
     * Previously: Appointment status changed, then dentist update (could crash between)
     * Now: Both wrapped in transaction (all-or-nothing)
     */
    public function transitionTo(Appointment $appointment, string $newState, string $reason = ''): bool
    {
        $currentState = $appointment->status->value; // GET THE STRING VALUE, NOT THE ENUM OBJECT

        // Validate transition
        if (!$this->isValidTransition($currentState, $newState)) {
            Log::warning("Invalid state transition", [
                'appointment_id' => $appointment->id,
                'from' => $currentState,
                'to' => $newState,
            ]);
            return false;
        }

        // Cannot transition from terminal state
        if (in_array($currentState, self::TERMINAL_STATES)) {
            Log::warning("Cannot transition from terminal state", [
                'appointment_id' => $appointment->id,
                'state' => $currentState,
            ]);
            return false;
        }

        // CRIT-005: Wrap both status and dentist updates in atomic transaction
        try {
            DB::transaction(function () use ($appointment, $newState, $currentState, $reason) {
                // Update appointment status
                $appointment->update(['status' => $newState]);

                // Log state change - pass array directly (model casts to JSON)
                ActivityLogger::log(
                    action: 'appointment_state_change',
                    modelType: 'Appointment',
                    modelId: $appointment->id,
                    description: "Appointment transitioned from {$currentState} to {$newState}",
                    newValues: [
                        'visit_code' => $appointment->visit_code,
                        'from_state' => $currentState,
                        'to_state' => $newState,
                        'reason' => $reason,
                    ]
                );

                // Refresh appointment to clear cached relationships before automation
                $appointment->refresh();

                // Trigger automation for new state (includes atomic dentist updates)
                $this->triggerStateAutomation($appointment, $newState);
            }, 3);

            // CRITICAL FIX: Reload appointment after transaction to get actual final status
            // (onCompleted may have updated status again to feedback_scheduled)
            $appointment->refresh();
            $finalStatus = $appointment->status->value;

            // Dispatch event AFTER successful transition with ACTUAL final status
            // This triggers WhatsApp notifications, queue management, and dashboard updates
            AppointmentStateChanged::dispatch(
                $appointment,
                $currentState,
                $finalStatus,
                $reason
            );

            return true;
        } catch (\Exception $e) {
            Log::error("State transition failed", [
                'appointment_id' => $appointment->id,
                'from' => $currentState,
                'to' => $newState,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Check if transition is allowed
     */
    public function isValidTransition(string $from, string $to): bool
    {
        return isset(self::ALLOWED_TRANSITIONS[$from]) &&
               in_array($to, self::ALLOWED_TRANSITIONS[$from]);
    }

    /**
     * Trigger all automations for a state
     */
    private function triggerStateAutomation(Appointment $appointment, string $newState): void
    {
        match($newState) {
            self::STATE_CHECKED_IN => $this->onCheckedIn($appointment),
            self::STATE_WAITING => $this->onWaiting($appointment),
            self::STATE_IN_TREATMENT => $this->onInTreatment($appointment),
            self::STATE_COMPLETED => $this->onCompleted($appointment),
            default => null,
        };
    }

    /**
     * Automation for CHECKED_IN state
     * Create queue number
     */
    private function onCheckedIn(Appointment $appointment): void
    {
        Log::info("onCheckedIn called", ['appointment_id' => $appointment->id]);
        
        // Generate queue number if not exists
        if (!$appointment->queue) {
            Log::info("No queue exists, creating one", ['appointment_id' => $appointment->id]);
            $queueNumber = $this->generateQueueNumber($appointment->clinic_location);
            
            Queue::create([
                'appointment_id' => $appointment->id,
                'queue_number' => $queueNumber,
                'queue_status' => 'waiting',
            ]);

            $appointment->update(['checked_in_at' => now()]);

            Log::info("Queue created on check-in", [
                'appointment_id' => $appointment->id,
                'queue_number' => $queueNumber,
            ]);
        } else {
            Log::info("Queue already exists, skipping creation", [
                'appointment_id' => $appointment->id,
                'queue_id' => $appointment->queue->id,
            ]);
        }
        
        // CRITICAL: Always transition to waiting, regardless of whether queue existed
        // This ensures appointments are never stuck in 'checked_in' state
        Log::info("Auto-transitioning to waiting after queue (re)check", ['appointment_id' => $appointment->id]);
        $appointment->update(['status' => self::STATE_WAITING]);
        
        // Refresh to get updated status for onWaiting
        $appointment->refresh();
        
        // Trigger onWaiting automation
        $this->onWaiting($appointment);
        
        Log::info("Auto-transitioned to waiting", ['appointment_id' => $appointment->id]);
    }

    /**
     * Automation for WAITING state
     * Patient appears on queue board
     */
    private function onWaiting(Appointment $appointment): void
    {
        // Queue should exist from CHECKED_IN state
        if ($appointment->queue) {
            $appointment->queue->update(['queue_status' => 'waiting']);
        }

        Log::info("Patient now waiting", [
            'appointment_id' => $appointment->id,
            'queue_number' => $appointment->queue?->queue_number,
        ]);
        
        // ✅ NEW: Try to automatically assign to available room/dentist
        // If a room + dentist is available, call the patient immediately
        // This prevents unnecessary waits when resources are idle
        $this->tryAutoAssignToRoom($appointment);
    }
    
    /**
     * Automatically assign waiting patient to available room + dentist
     * Called when patient enters waiting state if resources are available
     * 
     * ✅ CRITICAL: Only checks for resources - assignment happens via state machine
     * ✅ Uses QueueAssignmentService which handles all atomic updates
     * ✅ Silently fails if resources unavailable (patient remains waiting)
     * 
     * @param Appointment $appointment
     * @return bool True if assignment successful, false if no resources available
     */
    private function tryAutoAssignToRoom(Appointment $appointment): bool
    {
        // Log that we're attempting auto-assign
        Log::info('🤖 Attempting auto-assign to room', [
            'appointment_id' => $appointment->id,
            'patient_name' => $appointment->patient_name,
            'status' => $appointment->status?->value,
        ]);
        
        try {
            // Only auto-assign if still in waiting state
            if (!$appointment->queue) {
                Log::debug('Auto-assign skipped: no queue entry', [
                    'appointment_id' => $appointment->id,
                ]);
                return false;
            }
            
            if ($appointment->queue->queue_status !== 'waiting') {
                Log::debug('Auto-assign skipped: queue not in waiting status', [
                    'appointment_id' => $appointment->id,
                    'queue_status' => $appointment->queue->queue_status,
                ]);
                return false;
            }
            
            // Check if queue pause is active
            $queueSettings = \DB::table('queue_settings')->first();
            if ($queueSettings && $queueSettings->is_paused) {
                Log::info('Auto-assign skipped: queue is paused', [
                    'appointment_id' => $appointment->id,
                ]);
                return false;
            }
            
            // Verify appointment is still waiting
            $appointment->refresh();
            if ($appointment->status->value !== 'waiting') {
                Log::debug('Auto-assign skipped: status changed', [
                    'appointment_id' => $appointment->id,
                    'current_status' => $appointment->status->value,
                ]);
                return false;
            }
            
            Log::info('Auto-assign: Attempting assignment with QueueAssignmentService', [
                'queue_id' => $appointment->queue->id,
                'queue_number' => $appointment->queue->queue_number,
            ]);
            
            // Try to use QueueAssignmentService to assign automatically
            $assignmentService = app(\App\Services\QueueAssignmentService::class);
            $assignedQueue = $assignmentService->assignPatientToQueue($appointment->queue, $appointment->clinic_location);
            
            if ($assignedQueue) {
                // Refresh appointment to get updated status from state machine
                $appointment->refresh();
                
                Log::info('✅ AUTO-ASSIGN SUCCESS: Patient assigned to room', [
                    'appointment_id' => $appointment->id,
                    'new_status' => $appointment->status->value,
                    'queue_id' => $assignedQueue->id,
                    'room_id' => $assignedQueue->room_id,
                    'dentist_id' => $assignedQueue->dentist_id,
                ]);
                return true;
            } else {
                Log::info('Auto-assign failed: No available room or dentist', [
                    'appointment_id' => $appointment->id,
                    'queue_number' => $appointment->queue->queue_number,
                    'clinic_location' => $appointment->clinic_location,
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::warning('⚠️  Auto-assign exception occurred', [
                'appointment_id' => $appointment->id,
                'error_message' => $e->getMessage(),
                'error_class' => get_class($e),
                'error_file' => $e->getFile() . ':' . $e->getLine(),
            ]);
            // Don't fail the whole check-in - patient remains waiting
            return false;
        }
    }

    /**
     * Automation for IN_TREATMENT state
     * Send "go to room" WhatsApp, update queue status, mark dentist BUSY, mark room OCCUPIED
     */
    private function onInTreatment(Appointment $appointment): void
    {
        $appointment->update(['treatment_started_at' => now()]);

        if ($appointment->queue) {
            $appointment->queue->update(['queue_status' => 'in_treatment']);
        }

        // Mark dentist as BUSY (now treating patient)
        if ($appointment->dentist) {
            $this->dentistService->setBusy($appointment->dentist);
            
            Log::info("Dentist marked BUSY - treatment started", [
                'appointment_id' => $appointment->id,
                'dentist_id' => $appointment->dentist_id,
                'dentist_name' => $appointment->dentist->name,
            ]);
        }

        // Mark room as OCCUPIED (treatment room is now in use)
        if ($appointment->queue?->room) {
            $appointment->queue->room->update(['status' => 'occupied']);
            
            Log::info("Room marked OCCUPIED - treatment started", [
                'appointment_id' => $appointment->id,
                'room_id' => $appointment->queue->room->id,
                'room_number' => $appointment->queue->room->room_number,
            ]);
        }

        // Send WhatsApp with room info
        if ($appointment->patient_phone && $appointment->queue?->room) {
            /**
             * HIGH-004 FIX: Implement retry logic for WhatsApp failures
             * Previously: Caught exception but only logged (no retry)
             * Now: Attempts retry with exponential backoff
             */
            $maxRetries = 3;
            $retryDelay = 1; // seconds
            $sent = false;

            for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
                try {
                    $whatsAppSender = new WhatsAppSender();
                    $whatsAppSender->sendTreatmentStartMessage(
                        phone: $appointment->patient_phone,
                        patientName: $appointment->patient_name,
                        queueNumber: $appointment->queue?->queue_number,
                        room: $appointment->queue->room->room_number,
                    );
                    $sent = true;
                    break;  // Successfully sent, exit retry loop
                } catch (\Exception $e) {
                    Log::warning("WhatsApp send attempt {$attempt} of {$maxRetries} failed", [
                        'appointment_id' => $appointment->id,
                        'phone' => $appointment->patient_phone,
                        'attempt' => $attempt,
                        'error' => $e->getMessage(),
                    ]);

                    // Wait before retrying (except on last attempt)
                    if ($attempt < $maxRetries) {
                        sleep($retryDelay);
                        $retryDelay *= 2;  // Exponential backoff
                    }
                }
            }

            if (!$sent) {
                // After all retries failed, log critical error
                Log::error("Failed to send treatment WhatsApp after {$maxRetries} attempts", [
                    'appointment_id' => $appointment->id,
                    'phone' => $appointment->patient_phone,
                    'room' => $appointment->queue?->room?->room_number,
                ]);
                // Note: Appointment continues despite failed notification
                // Patient may check queue board or staff will see them
            }
        }

        Log::info("Patient treatment started", [
            'appointment_id' => $appointment->id,
            'room' => $appointment->queue?->room?->room_number,
            'dentist_id' => $appointment->dentist_id,
        ]);
    }

    /**
     * Automation for COMPLETED state
     * Free room/dentist, send feedback link instantly, mark dentist AVAILABLE
     */
    private function onCompleted(Appointment $appointment): void
    {
        $appointment->update(['treatment_ended_at' => now()]);

        // Mark dentist as AVAILABLE (treatment finished, ready for next patient)
        if ($appointment->dentist) {
            $this->dentistService->setAvailable($appointment->dentist);
            
            Log::info("Dentist marked AVAILABLE - treatment completed", [
                'appointment_id' => $appointment->id,
                'dentist_id' => $appointment->dentist_id,
                'dentist_name' => $appointment->dentist->name,
            ]);
        }

        // CRITICAL: Release the room so next patient can use it
        // Ensure queue and room are loaded with fresh data
        if ($appointment->queue) {
            // Reload queue with room relationship to ensure we have fresh data
            $appointment->queue->load('room');
            
            if ($appointment->queue->room) {
                $appointment->queue->room->markAvailable();
                
                Log::info("Room released after treatment completion", [
                    'appointment_id' => $appointment->id,
                    'room_id' => $appointment->queue->treatment_room_id,
                    'room_number' => $appointment->queue->room->room_number,
                ]);
            }
            
            // Update queue status
            $appointment->queue->update(['queue_status' => 'completed']);
        } else {
            Log::warning("No queue found for appointment when completing treatment", [
                'appointment_id' => $appointment->id,
            ]);
        }

        // IMPORTANT: Feedback scheduling is now done ASYNCHRONOUSLY
        // This prevents exceptions in WhatsApp API from breaking the state transition
        // Set appointment to feedback_scheduled stage
        $appointment->update(['status' => self::STATE_FEEDBACK_SCHEDULED]);
        
        ActivityLogger::log(
            action: 'appointment_state_change',
            modelType: 'Appointment',
            modelId: $appointment->id,
            description: "Appointment transitioned to feedback_scheduled",
            newValues: [
                'from_state' => 'completed',
                'to_state' => 'feedback_scheduled',
                'reason' => 'Feedback link will be sent asynchronously'
            ]
        );

        Log::info("Patient treatment completed - feedback will be sent asynchronously", [
            'appointment_id' => $appointment->id,
        ]);
    }

    /**
     * Generate next queue number for clinic location
     */
    private function generateQueueNumber(string $clinicLocation): int
    {
        $today = Carbon::today();
        
        $lastQueue = Queue::whereHas('appointment', function ($q) use ($today, $clinicLocation) {
            $q->whereDate('appointment_date', $today)
              ->where('clinic_location', $clinicLocation);  // Filter by clinic in appointment
        })
        ->orderBy('queue_number', 'desc')
        ->first();

        $queueNumber = ($lastQueue?->queue_number ?? 0) + 1;
        
        Log::info("Generated queue number", [
            'clinic_location' => $clinicLocation,
            'last_queue' => $lastQueue?->queue_number,
            'generated_number' => $queueNumber,
        ]);
        
        return $queueNumber;
    }

    /**
     * Get all allowed next states for current state
     */
    public function getAllowedNextStates(string $currentState): array
    {
        if (in_array($currentState, self::TERMINAL_STATES)) {
            return [];
        }

        return self::ALLOWED_TRANSITIONS[$currentState] ?? [];
    }

    /**
     * Check if state is terminal
     */
    public function isTerminalState(string $state): bool
    {
        return in_array($state, self::TERMINAL_STATES);
    }

    /**
     * Get human-readable state label
     */
    public function getStateLabel(string $state): string
    {
        return match($state) {
            self::STATE_BOOKED => 'Booked',
            self::STATE_CONFIRMED => 'Confirmed',
            self::STATE_CANCELLED => 'Cancelled',
            self::STATE_NO_SHOW => 'No Show',
            self::STATE_CHECKED_IN => 'Checked In',
            self::STATE_WAITING => 'Waiting',
            self::STATE_IN_TREATMENT => 'In Treatment',
            self::STATE_COMPLETED => 'Completed',
            self::STATE_FEEDBACK_SCHEDULED => 'Feedback Scheduled',
            self::STATE_FEEDBACK_SENT => 'Feedback Sent',
            default => 'Unknown',
        };
    }

    /**
     * Get state color for UI
     */
    public function getStateColor(string $state): string
    {
        return match($state) {
            self::STATE_BOOKED => 'secondary',
            self::STATE_CONFIRMED => 'info',
            self::STATE_CHECKED_IN => 'primary',
            self::STATE_WAITING => 'warning',
            self::STATE_IN_TREATMENT => 'success',
            self::STATE_COMPLETED => 'success',
            self::STATE_FEEDBACK_SCHEDULED => 'info',
            self::STATE_FEEDBACK_SENT => 'secondary',
            self::STATE_CANCELLED => 'danger',
            self::STATE_NO_SHOW => 'danger',
            default => 'secondary',
        };
    }
}
