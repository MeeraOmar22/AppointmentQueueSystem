<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\Room;
use App\Models\Dentist;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogger;

class QueueAssignmentService
{
    /**
     * Automatically assign next waiting patient to available room/dentist
     * ENFORCES FIFO: Always selects lowest queue number, staff cannot override
     * ENFORCES DENTIST AVAILABILITY: Returns error if no dentist available
     * ENFORCES MANDATORY DENTIST ASSIGNMENT: Patient enters in_treatment ONLY with assigned dentist
     * FIXED: Now uses pessimistic locking to prevent concurrent race conditions
     * 
     * CRITICAL BUSINESS RULE:
     * - Patient status = in_treatment ONLY IF dentist_id IS NOT NULL
     * - Never allow: status=in_treatment AND dentist_id=NULL
     * - Never allow: status=in_treatment AND room_id=NULL
     * 
     * @param string $clinicLocation
     * @return Queue|null
     */
    public function assignNextPatient(string $clinicLocation = 'seremban'): ?Queue
    {
        return DB::transaction(function () use ($clinicLocation) {
            // CHECK: Queue pause status first
            $queueSettings = DB::table('queue_settings')->first();
            
            if ($queueSettings && $queueSettings->is_paused) {
                logger()->info('Queue assignment skipped: queue is paused', ['clinic_location' => $clinicLocation]);
                return null;
            }

            // FIXED: Use lockForUpdate() to prevent concurrent requests from selecting same patient
            // This ensures only ONE staff member can assign the next patient
            $queue = Queue::where('queue_status', 'waiting')
                ->whereHas('appointment', function ($query) use ($clinicLocation) {
                    $query->whereDate('appointment_date', Carbon::today())
                        ->where('clinic_location', $clinicLocation);
                })
                ->orderBy('queue_number') // FIFO: lowest queue number first
                ->lockForUpdate() // ← CRITICAL: Pessimistic lock prevents race condition
                ->first();

            if (!$queue) {
                return null;
            }

            // Check dentist availability FIRST - critical for operations
            $dentist = $this->findAvailableDentist($clinicLocation, $queue->appointment);
            if (!$dentist) {
                // No dentist available: cannot proceed
                // Patient MUST remain in waiting state
                return null;
            }

            // Find available room with lock to prevent double-booking
            // CRITICAL: Check both status column AND active in_treatment appointments
            // Only exclude rooms where BOTH queue AND appointment are in treatment
            $activeRoomIds = Queue::where('queue_status', 'in_treatment')
                ->whereHas('appointment', function($query) {
                    // Only count as active if appointment is also in_treatment
                    // This prevents counting completed appointments that haven't updated queue status yet
                    $query->where('status', 'in_treatment');
                })
                ->pluck('room_id')
                ->toArray();
            
            $room = Room::where('clinic_location', $clinicLocation)
                ->where('is_active', true)
                ->whereNotIn('id', $activeRoomIds)  // Exclude rooms with active treatment
                ->where(function ($query) {
                    // Prefer rooms marked as available, but also accept rooms not yet updated
                    $query->where('status', 'available')
                        ->orWhereNull('status');
                })
                ->orderBy('room_number')
                ->lockForUpdate() // ← Prevent another transaction from selecting same room
                ->first();
                
            if (!$room) {
                // No room available: cannot proceed
                // Patient MUST remain in waiting state
                return null;
            }

            // ========== CRITICAL: ATOMIC ASSIGNMENT ==========
            // All updates must be atomic to prevent partial state
            
            // 1. Transition appointment to IN_TREATMENT using state machine BEFORE direct updates
            $appointmentStateService = app(AppointmentStateService::class);
            if (!$appointmentStateService->transitionTo($queue->appointment, 'in_treatment', 'Called to treatment room')) {
                // State transition failed - appointment might be in terminal state or invalid transition
                $currentStatus = $queue->appointment->status?->value ?? 'unknown';
                logger()->warning('State transition failed for appointment in assignNextPatient', [
                    'appointment_id' => $queue->appointment->id,
                    'current_status' => $currentStatus,
                ]);
                throw new \Exception("Cannot transition appointment from '{$currentStatus}' to 'in_treatment'. Appointment may already be completed or cancelled.");
            }
            
            // Refresh to get updated status after state transition
            $queue->appointment->refresh();
            
            // 2. Update queue with resource assignment
            $queue->update([
                'queue_status' => 'in_treatment',
                'room_id' => $room->id,
                'dentist_id' => $dentist->id,
            ]);

            // 3. Mark room as occupied to prevent double-booking
            // Only ONE patient can use a room at a time
            $room->markOccupied();

            // 4. Mark dentist as busy to prevent double-booking
            // Only ONE patient can be treated by a dentist at a time
            $dentist->update(['status' => false]); // false = busy

            // 5. Update appointment with dentist, room and actual_start_time
            $queue->appointment->update([
                'dentist_id' => $dentist->id,
                'room' => $room->room_number, // Store room number in appointment's room field
                'actual_start_time' => now(),
            ]);

            // 6. Log assignment with all resources
            ActivityLogger::log(
                'queue_assigned',
                'Appointment',
                $queue->appointment->id,
                'Assigned to Room ' . $room->room_number . ' with Dr. ' . $dentist->name,
                null,
                ['room_id' => $room->id, 'dentist_id' => $dentist->id, 'queue_status' => 'called']
            );

            // ========== ASSIGNMENT COMPLETE ==========
            // Patient is now in_treatment with BOTH dentist AND room assigned
            // This transaction is atomic - all updates succeed or all rollback

            return $queue;
        }, 3); // Retry transaction if it fails (deadlock handling)
    }

    /**
     * Assign a SPECIFIC patient (from queue) to available dentist and room
     * 
     * Used when staff calls a specific waiting patient (not FIFO)
     * Enforces the same mandatory assignment rules as assignNextPatient()
     * 
     * CRITICAL BUSINESS RULE:
     * - Patient status = in_treatment ONLY IF dentist_id IS NOT NULL
     * - Never allow: status=in_treatment AND dentist_id=NULL
     * - Never allow: status=in_treatment AND room_id=NULL
     * 
     * @param Queue $queue The queue entry for the patient to call
     * @param string $clinicLocation
     * @return Queue|null The updated queue with assigned resources, or null if assignment failed
     */
    public function assignPatientToQueue(Queue $queue, string $clinicLocation = 'seremban'): ?Queue
    {
        return DB::transaction(function () use ($queue, $clinicLocation) {
            // CHECK: Queue pause status first
            $queueSettings = DB::table('queue_settings')->first();
            
            if ($queueSettings && $queueSettings->is_paused) {
                logger()->info('Patient call skipped: queue is paused', [
                    'queue_id' => $queue->id,
                    'clinic_location' => $clinicLocation
                ]);
                return null;
            }

            // Lock this queue entry to prevent race conditions
            $queue = Queue::lockForUpdate()->findOrFail($queue->id);

            // Verify queue is still in waiting state
            if ($queue->queue_status !== 'waiting') {
                return null; // Cannot call patient that's not waiting
            }

            // Check dentist availability - critical resource check
            $dentist = $this->findAvailableDentist($clinicLocation, $queue->appointment);
            if (!$dentist) {
                // No dentist available: cannot proceed
                return null;
            }

            // Find available room with lock to prevent double-booking
            // CRITICAL: Check both status column AND active in_treatment appointments
            // Only exclude rooms where BOTH queue AND appointment are in treatment
            $activeRoomIds = Queue::where('queue_status', 'in_treatment')
                ->whereHas('appointment', function($query) {
                    // Only count as active if appointment is also in_treatment
                    // This prevents counting completed appointments that haven't updated queue status yet
                    $query->where('status', 'in_treatment');
                })
                ->pluck('room_id')
                ->toArray();
            
            $room = Room::where('clinic_location', $clinicLocation)
                ->where('is_active', true)
                ->whereNotIn('id', $activeRoomIds)  // Exclude rooms with active treatment
                ->where(function ($query) {
                    // Prefer rooms marked as available, but also accept rooms not yet updated
                    $query->where('status', 'available')
                        ->orWhereNull('status');
                })
                ->orderBy('room_number')
                ->lockForUpdate()
                ->first();
                
            if (!$room) {
                // No room available: cannot proceed
                logger()->warning('No available room found for assignment', [
                    'clinic_location' => $clinicLocation,
                    'active_room_ids' => $activeRoomIds,
                ]);
                return null;
            }

            // ========== CRITICAL: ATOMIC ASSIGNMENT ==========
            // All updates must be atomic to prevent partial state
            // This is identical to assignNextPatient() logic - ensures consistency
            
            // 1. Transition appointment to IN_TREATMENT using state machine BEFORE updating status
            $appointmentStateService = app(AppointmentStateService::class);
            if (!$appointmentStateService->transitionTo($queue->appointment, 'in_treatment', 'Called to treatment room')) {
                // State transition failed - appointment might be in terminal state or invalid transition
                $currentStatus = $queue->appointment->status?->value ?? 'unknown';
                logger()->warning('State transition failed for appointment in assignPatientToQueue', [
                    'appointment_id' => $queue->appointment->id,
                    'current_status' => $currentStatus,
                ]);
                throw new \Exception("Cannot transition appointment from '{$currentStatus}' to 'in_treatment'. Appointment may already be completed or cancelled.");
            }
            
            // Refresh to get updated status after state transition
            $queue->appointment->refresh();
            
            // CRITICAL VALIDATION: Verify appointment status was updated
            if ($queue->appointment->status->value !== 'in_treatment') {
                logger()->error('Appointment status not updated after state transition', [
                    'appointment_id' => $queue->appointment->id,
                    'current_status' => $queue->appointment->status->value,
                    'expected' => 'in_treatment',
                ]);
                throw new \Exception('Appointment status failed to transition to in_treatment');
            }
            
            // 2. Update queue with resource assignment
            $queue->update([
                'queue_status' => 'in_treatment',
                'room_id' => $room->id,
                'dentist_id' => $dentist->id,
            ]);
            
            // VALIDATION: Verify queue was updated with resources
            if (!$queue->room_id || !$queue->dentist_id) {
                logger()->error('Queue resource assignment failed in assignNextPatient', [
                    'queue_id' => $queue->id,
                    'room_id' => $queue->room_id,
                    'dentist_id' => $queue->dentist_id,
                ]);
                throw new \Exception('Failed to assign room or dentist to queue');
            }

            // 3. Mark room as occupied to prevent double-booking
            $room->markOccupied();

            // 4. Mark dentist as busy to prevent double-booking
            $dentist->update(['status' => false]); // false = busy

            // 5. Update appointment with dentist, room and actual_start_time
            $queue->appointment->update([
                'dentist_id' => $dentist->id,
                'room' => $room->room_number, // Store room number in appointment's room field
                'actual_start_time' => now(),
            ]);

            // 6. Log assignment with all resources
            ActivityLogger::log(
                'queue_assigned',
                'Appointment',
                $queue->appointment->id,
                'Assigned to Room ' . $room->room_number . ' with Dr. ' . $dentist->name,
                null,
                ['room_id' => $room->id, 'dentist_id' => $dentist->id, 'queue_status' => 'in_treatment']
            );

            // Final check before returning - ensure we don't have in_treatment without room/dentist
            $queue->refresh();
            if ($queue->queue_status === 'in_treatment' && (!$queue->room_id || !$queue->dentist_id)) {
                logger()->error('CRITICAL: in_treatment without room/dentist - invalid state', [
                    'queue_id' => $queue->id,
                    'room_id' => $queue->room_id,
                    'dentist_id' => $queue->dentist_id,
                ]);
                throw new \Exception('CRITICAL: Patient marked in_treatment but room or dentist missing');
            }
            
            // FINAL VALIDATION: Ensure complete status synchronization before returning
            $queue->appointment->refresh();
            if ($queue->queue_status !== 'in_treatment' || $queue->appointment->status->value !== 'in_treatment') {
                logger()->critical('CRITICAL: Status sync validation failed - out of sync', [
                    'queue_status' => $queue->queue_status,
                    'appointment_status' => $queue->appointment->status->value,
                    'both_should_be' => 'in_treatment',
                ]);
                throw new \Exception('CRITICAL: Status synchronization failed - queue and appointment states are out of sync');
            }

            // ========== ASSIGNMENT COMPLETE ==========
            // Patient is now in_treatment with BOTH dentist AND room assigned
            // This transaction is atomic - all updates succeed or all rollback

            return $queue;
        }, 3); // Retry transaction if it fails (deadlock handling)
    }

    /**
     * Start treatment for a queue entry
     * 
     * @param Queue $queue
     * @return void
     */
    public function startTreatment(Queue $queue): void
    {
        DB::transaction(function () use ($queue) {
            $queue->markInTreatment();
            $queue->appointment->markInTreatment();

            ActivityLogger::log(
                'treatment_started',
                'Appointment',
                $queue->appointment->id,
                'Treatment started in Room ' . $queue->room?->room_number,
                ['status' => $queue->appointment->status],
                ['status' => 'in_treatment', 'room' => $queue->room?->room_number]
            );
        });
    }

    /**
     * Complete treatment for a queue entry
     * 
     * CRITICAL: Release both room AND dentist when treatment completes
     * This allows both resources to be assigned to next patient
     * 
     * @param Queue $queue
     * @return void
     */
    public function completeTreatment(Queue $queue): void
    {
        DB::transaction(function () use ($queue) {
            $queue->markCompleted();
            $queue->appointment->markCompleted();

            // CRITICAL: Release the room so next patient can use it
            // This is essential for resource-aware queue management
            if ($queue->room) {
                $queue->room->markAvailable();
            }

            // CRITICAL: Release the dentist so they can treat next patient
            // This is essential for dentist availability management
            if ($queue->appointment->dentist) {
                $queue->appointment->dentist->update(['status' => true]); // true = available
            }

            // Set actual end time when treatment completes
            $queue->appointment->update([
                'actual_end_time' => now(),
            ]);

            ActivityLogger::log(
                'treatment_completed',
                'Appointment',
                $queue->appointment->id,
                'Treatment completed',
                ['status' => 'in_treatment'],
                ['status' => 'completed']
            );

            // Try to assign next patient to freed room/dentist
            $this->assignNextPatient($queue->appointment->clinic_location);
        });
    }

    /**
     * Find first available room
     * 
     * CRITICAL: Must check multiple conditions:
     * 1. Is active (admin hasn't disabled it with is_active=false)
     * 2. Status is available
     * 3. Not currently occupied by another patient in treatment
     * 4. Has capacity available (FIX #11: validate room capacity)
     * 
     * @param string $clinicLocation
     * @return Room|null
     */
    private function findAvailableRoom(string $clinicLocation = 'seremban'): ?Room
    {
        // FIX #11: Count current active patients in each room
        $roomsWithPatients = Queue::select('room_id')
            ->selectRaw('COUNT(*) as patient_count')
            ->where('queue_status', 'in_treatment')
            ->whereHas('appointment', function($query) {
                $query->where('status', 'in_treatment');
            })
            ->groupBy('room_id')
            ->get()
            ->keyBy('room_id');

        $room = Room::where('clinic_location', $clinicLocation)
            ->where('status', 'available')
            ->where('is_active', true)  // CRITICAL: Only active rooms (admin hasn't deactivated)
            ->orderBy('room_number')
            ->first();

        // FIX #11: Validate room capacity not exceeded
        if ($room && isset($roomsWithPatients[$room->id])) {
            $patientCount = $roomsWithPatients[$room->id]->patient_count;
            if ($patientCount >= $room->capacity) {
                // This room is at capacity, find next available
                return Room::where('clinic_location', $clinicLocation)
                    ->where('status', 'available')
                    ->where('is_active', true)
                    ->where('id', '!=', $room->id)
                    ->orderBy('room_number')
                    ->first();
            }
        }

        return $room;
    }

    /**
     * Find available dentist
     * Prefers the assigned dentist if available, otherwise picks any available
     * 
     * CRITICAL: Dentist availability is checked ACROSS ALL LOCATIONS
     * Dentists work at multiple clinics and cannot be double-booked
     * If a dentist is in_treatment at Seremban, they're unavailable at Kuala Pilah too
     * 
     * CRITICAL: Checks dentist.status, active appointments, AND leave dates
     * Prevents assigning a dentist who's already treating another patient or on leave
     * 
     * FIX #12: Also validate dentist.status === true (must be active)
     * Prevents assigning dentists who have been deactivated by admin
     * 
     * @param string $clinicLocation
     * @param Appointment $appointment
     * @return Dentist|null
     */
    private function findAvailableDentist(string $clinicLocation, Appointment $appointment): ?Dentist
    {
        $today = Carbon::today();
        
        // FIX #9: Get list of dentists CURRENTLY TREATING PATIENTS (across all locations)
        // This is intentional - dentists cannot be in two places simultaneously
        // Even if patient is from another clinic, we exclude dentists in_treatment everywhere
        $busyDentistIds = Queue::where('queue_status', 'in_treatment')
            ->whereHas('appointment', function($query) {
                $query->where('status', 'in_treatment');
            })
            ->pluck('dentist_id')
            ->toArray();

        // First try the assigned dentist if not treating anyone else
        if ($appointment->dentist) {
            // FIX #12: CRITICAL - Check dentist.status === true (admin must have activated them)
            if ($appointment->dentist->status === true) {
                // Also check they're not treating another patient
                if (!in_array($appointment->dentist->id, $busyDentistIds)) {
                    // Check if dentist is on leave today
                    $onLeave = $appointment->dentist->leaves()
                        ->where('start_date', '<=', $today)
                        ->where('end_date', '>=', $today)
                        ->exists();
                    
                    if (!$onLeave) {
                        return $appointment->dentist;
                    }
                }
            }
        }

        // Otherwise pick any available dentist (status is boolean: 1 = available, 0 = busy)
        // CRITICAL: Exclude dentists already treating patients AND dentists on leave
        // FIX #12: MUST validate status === true (only active dentists)
        return Dentist::where('status', true)
            ->whereNotIn('id', $busyDentistIds)
            ->whereDoesntHave('leaves', function($query) use ($today) {
                // Exclude dentists with active leave (where today falls within leave period)
                $query->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today);
            })
            ->orderBy('name')
            ->first();
    }

    /**
     * Get estimated wait time for a patient in queue
     * 
     * ✅ NOW: Uses consolidated EstimatedWaitTimeService for consistency
     * This ensures all ETA calculations (staff dashboard, public board, patient tracking) are identical
     * 
     * @param Queue $queue
     * @return int Minutes
     */
    public function getEstimatedWaitTime(Queue $queue): int
    {
        $etaService = new EstimatedWaitTimeService();
        return $etaService->getETAForAppointment($queue->appointment) ?? 0;
    }

    /**
     * DEPRECATED: Old ETA calculation - kept for reference
     * Use EstimatedWaitTimeService instead for all future implementations
     * 
     * Old logic:
     * - Summed all patients ahead durations
     * - Ignored room availability
     * 
     * Problems with old approach:
     * - Did not account for concurrent treatment in multiple rooms
     * - Would always show high ETAs even with multiple rooms available
     */
    private function getEstimatedWaitTime_DEPRECATED(Queue $queue): int
    {
        if ($queue->isInTreatment()) {
            return 0;
        }

        $totalWaitTime = 0;

        // Get all patients ahead in queue
        $patientsAhead = Queue::where('queue_number', '<', $queue->queue_number)
            ->where('queue_status', '!=', 'completed')
            ->whereHas('appointment', function ($query) use ($queue) {
                $query->where('clinic_location', $queue->appointment->clinic_location);
            })
            ->orderBy('queue_number')
            ->get();

        foreach ($patientsAhead as $ahead) {
            if ($ahead->isInTreatment()) {
                // Add remaining treatment time estimate
                $totalWaitTime += $this->getEstimatedTreatmentDuration($ahead);
            } else if ($ahead->isWaiting()) {
                // Add estimated treatment duration for waiting patients
                $totalWaitTime += $this->getEstimatedTreatmentDuration($ahead);
            }
        }

        return $totalWaitTime;
    }

    /**
     * Get estimated treatment duration for a queue entry
     * 
     * @param Queue $queue
     * @return int
     */
    private function getEstimatedTreatmentDuration(Queue $queue): int
    {
        return max((int) ($queue->appointment->service->estimated_duration ?? 0), 15);
    }

    /**
     * Check if all rooms are occupied
     * 
     * @param string $clinicLocation
     * @return bool
     */
    public function allRoomsOccupied(string $clinicLocation = 'seremban'): bool
    {
        $availableRooms = Room::where('clinic_location', $clinicLocation)
            ->where('status', 'available')
            ->count();

        return $availableRooms === 0;
    }

    /**
     * Get queue statistics for today
     * 
     * @param string $clinicLocation
     * @return array
     */
    public function getQueueStats(string $clinicLocation = 'seremban'): array
    {
        $today = Carbon::today();

        return [
            'total_appointments' => Appointment::whereDate('appointment_date', $today)
                ->where('clinic_location', $clinicLocation)
                ->count(),

            'checked_in' => Appointment::whereDate('appointment_date', $today)
                ->where('clinic_location', $clinicLocation)
                ->whereIn('status', ['arrived', 'in_queue', 'in_treatment', 'completed'])
                ->count(),

            'waiting' => Queue::where('queue_status', 'waiting')
                ->whereHas('appointment', function ($query) use ($today, $clinicLocation) {
                    $query->whereDate('appointment_date', $today)
                        ->where('clinic_location', $clinicLocation);
                })
                ->count(),

            'in_treatment' => Queue::where('queue_status', 'in_treatment')
                ->whereHas('appointment', function ($query) use ($today, $clinicLocation) {
                    $query->whereDate('appointment_date', $today)
                        ->where('clinic_location', $clinicLocation);
                })
                ->count(),

            'completed' => Queue::where('queue_status', 'completed')
                ->whereHas('appointment', function ($query) use ($today, $clinicLocation) {
                    $query->whereDate('appointment_date', $today)
                        ->where('clinic_location', $clinicLocation);
                })
                ->count(),

            'available_rooms' => Room::where('clinic_location', $clinicLocation)
                ->where('status', 'available')
                ->count(),

            'available_dentists' => Dentist::where('status', true)->count(),
        ];
    }
}
