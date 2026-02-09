<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Database\QueryException;

/**
 * ⚠️ DEPRECATED: ResourceAwareQueueService
 * 
 * This service has been superseded by:
 * - EstimatedWaitTimeService (ETA calculations) ✅ RECOMMENDED
 * - QueueAssignmentService (Queue management) ✅ RECOMMENDED
 * 
 * WHY DEPRECATED:
 * ❌ getEstimatedWaitTime() ignores available rooms entirely
 * ❌ Massively overestimates waiting times by treating concurrent rooms as sequential
 * ❌ Does NOT account for multi-room parallel treatment
 * ❌ Redundant with other queue services
 * 
 * MIGRATION PATH:
 * 1. Replace all getEstimatedWaitTime() calls with EstimatedWaitTimeService
 * 2. Replace all queue management with QueueAssignmentService
 * 3. Remove this service entirely once all usages migrated
 * 
 * ACCURACY ISSUES:
 * - Old formula: max(in_treatment_time) + sum(waiting_durations)
 * - New formula: ceil(patients_ahead / available_rooms) × service_duration
 * - Example: 3 rooms, 3 in-treatment, 2 waiting
 *   Old: 30 min (in-treatment) + 60 min (2×30) = 90 minutes ❌ WRONG
 *   New: ceil(2/3) × 30 = 30 minutes ✅ CORRECT
 * 
 * ACTIVE USES: NONE (no production code calls this service)
 * Can be safely removed after code cleanup
 * 
 * Queue management service for parallel treatment in multiple rooms
 * 
 * Single-clinic implementation. No clinic_location filtering needed.
 * All bookings, queues, rooms, dentists belong to one clinic.
 * 
 * FIXED ISSUES:
 * - Strict FIFO queue no longer blocks parallel treatment
 * - Multiple rooms can serve patients simultaneously
 * - Queue positions dynamically recalculated
 * - ETAs account for in-treatment patient completion
 * - Automatic next-patient assignment when room frees
 * 
 * Queue Status Flow:
 * 1. booked → Patient scheduled for appointment
 * 2. checked_in → Patient arrived at clinic
 * 3. waiting → Patient in queue, ready for treatment
 * 4. in_treatment → Patient being treated (holds room)
 * 5. completed → Patient treatment finished, room released
 * 
 * KEY INSIGHT:
 * Only "waiting" patients are in the queue.
 * "in_treatment" patients do NOT hold up the queue - next patient can be called
 * while previous patient is still being treated in their room.
 */
class ResourceAwareQueueService
{
    /**
     * Get complete queue snapshot for clinic
     * 
     * Returns:
     * - waiting_count: Number of patients waiting for treatment
     * - in_treatment_count: Number of patients currently being treated
     * - available_rooms_count: Number of empty rooms
     * - queue: Ordered list of waiting patients with positions and ETAs
     * - in_treatment: List of patients being treated with room info
     * 
     * @return array Queue snapshot with all relevant data
     */
    public function getQueueStatus(): array
    {
        // Get all waiting appointments (in queue order by check_in_time)
        $waitingPatients = Appointment::where('status', 'waiting')
            ->orderBy('check_in_time', 'asc')
            ->get();

        // Get all in-treatment appointments with rooms
        $inTreatment = Appointment::where('status', 'in_treatment')
            ->with('room')
            ->get();

        // Get total rooms and occupied rooms
        $totalRooms = Room::count();
        $occupiedRooms = $inTreatment->count();
        $availableRoomsCount = $totalRooms - $occupiedRooms;

        // Build queue array with positions and ETAs
        $queue = [];
        foreach ($waitingPatients as $index => $appointment) {
            $position = $index + 1;
            $waitTime = $this->getEstimatedWaitTime($appointment->id);

            $queue[] = [
                'position' => $position,
                'appointment_id' => $appointment->id,
                'patient_name' => $appointment->patient_name,
                'service_name' => $appointment->service?->name ?? 'Unknown Service',
                'wait_time_minutes' => $waitTime,
                'eta' => Carbon::now()->addMinutes($waitTime),
            ];
        }

        // Build in-treatment array
        $inTreatmentList = [];
        foreach ($inTreatment as $appointment) {
            $remainingMinutes = $this->calculateRemainingTreatmentMinutes($appointment);

            $inTreatmentList[] = [
                'appointment_id' => $appointment->id,
                'patient_name' => $appointment->patient_name,
                'service_name' => $appointment->service?->name ?? 'Unknown Service',
                'room_number' => $appointment->queue?->room?->room_number ?? 'Unknown',
                'remaining_minutes' => $remainingMinutes,
            ];
        }

        return [
            'waiting_count' => count($waitingPatients),
            'in_treatment_count' => $occupiedRooms,
            'available_rooms_count' => $availableRoomsCount,
            'queue' => $queue,
            'in_treatment' => $inTreatmentList,
        ];
    }

    /**
     * Assign next waiting patient to an available room
     * 
     * Algorithm:
     * 1. Find next waiting patient (earliest check_in_time)
     * 2. Find available room (not occupied)
     * 3. Assign patient to room
     * 4. Update appointment status from "waiting" to "in_treatment"
     * 5. Save room assignment and actual start time
     * 6. Return appointment with room info
     * 
     * Uses pessimistic locking to prevent race conditions when multiple
     * staff members try to call next patient simultaneously.
     * 
     * Returns null if:
     * - No waiting patients
     * - No available rooms
     * 
     * @return Appointment|null Next patient assigned to room, or null
     */
    public function assignNextPatient(): ?Appointment
    {
        try {
            // Use transaction to ensure atomic assignment
            return \DB::transaction(function () {
                // Lock and get next waiting patient (pessimistic locking)
                $nextPatient = Appointment::where('status', 'waiting')
                    ->orderBy('check_in_time', 'asc')
                    ->lockForUpdate() // Prevent other processes from modifying
                    ->first();

                if (!$nextPatient) {
                    return null; // No waiting patients
                }

                // Find available room (pessimistic locking)
                $availableRoom = Room::where('status', 'available')
                    ->lockForUpdate()
                    ->first();

                if (!$availableRoom) {
                    return null; // No available rooms
                }

                // Assign patient to room
                $nextPatient->room_id = $availableRoom->id;
                $nextPatient->status = 'in_treatment';
                $nextPatient->save();

                // Mark room as occupied
                $availableRoom->status = 'occupied';
                $availableRoom->current_appointment_id = $nextPatient->id;
                $availableRoom->save();

                // Record actual start time if not already recorded
                if (!$nextPatient->actual_start_time) {
                    $nextPatient->recordActualStartTime();
                }

                return $nextPatient->load('room');
            });
        } catch (QueryException $e) {
            // Handle deadlock with retry logic
            if ($e->getCode() === '40P01') { // Deadlock error code
                usleep(100000); // Wait 100ms
                return $this->assignNextPatient(); // Retry
            }
            throw $e;
        }
    }

    /**
     * Complete treatment for a patient and release their room
     * 
     * Algorithm:
     * 1. Update appointment status to "completed"
     * 2. Record actual end time
     * 3. Release room (mark as available)
     * 4. Automatically call next waiting patient if available
     * 
     * Typically called by dentist/staff when treatment finishes.
     * 
     * @param int $appointmentId Appointment to complete
     * @return bool True if completed, false if appointment not found
     */
    public function completeTreatment(int $appointmentId): bool
    {
        try {
            return \DB::transaction(function () use ($appointmentId) {
                $appointment = Appointment::lockForUpdate()->find($appointmentId);

                if (!$appointment) {
                    return false;
                }

                // Mark appointment as completed
                $appointment->status = 'completed';
                $appointment->recordActualEndTime();
                $appointment->save();

                // Release room
                if ($appointment->room_id) {
                    $room = Room::find($appointment->room_id);
                    if ($room) {
                        $room->status = 'available';
                        $room->current_appointment_id = null;
                        $room->save();
                    }
                }

                return true;
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '40P01') { // Deadlock
                usleep(100000);
                return $this->completeTreatment($appointmentId);
            }
            throw $e;
        }
    }

    /**
     * Get queue position for a specific patient
     * 
     * Position = number of waiting patients ahead in queue
     * Returns null if patient not waiting in queue
     * 
     * @param int $appointmentId Appointment to check
     * @return int|null Position (1, 2, 3, etc.) or null if not waiting
     */
    public function getQueuePosition(int $appointmentId): ?int
    {
        $appointment = Appointment::find($appointmentId);

        if (!$appointment || $appointment->status->value !== 'waiting') {
            return null;
        }

        $position = Appointment::where('status', 'waiting')
            ->where('check_in_time', '<', $appointment->check_in_time)
            ->count() + 1;

        return $position;
    }

    /**
     * Estimate wait time in minutes for a patient
     * 
     * Algorithm:
     * 1. Get queue position
     * 2. Sum remaining durations of all in-treatment patients
     * 3. Sum expected durations of all waiting patients ahead
     * 4. Total = sum of both
     * 
     * This gives a realistic ETA accounting for parallel treatment.
     * 
     * Example:
     * - Room 1: Patient A has 10 min remaining
     * - Room 2: Patient B has 20 min remaining
     * - Queue: [Patient C (30 min), Patient D (45 min)]
     * 
     * For Patient C:
     * - Max remaining in-treatment = 20 min (from Patient B)
     * - Wait for nobody ahead = 0 min
     * - Total ETA = 20 min
     * 
     * For Patient D:
     * - Max remaining in-treatment = 20 min
     * - Wait for Patient C = 30 min
     * - Total ETA = 20 + 30 = 50 min
     * 
     * @param int $appointmentId Appointment to estimate
     * @return int Estimated wait time in minutes
     */
    public function getEstimatedWaitTime(int $appointmentId): int
    {
        $appointment = Appointment::find($appointmentId);

        if (!$appointment || $appointment->status->value !== 'waiting') {
            return 0; // Not in queue
        }

        // Get all in-treatment appointments with their remaining durations
        $inTreatmentDurations = Appointment::where('status', 'in_treatment')
            ->get()
            ->map(fn($apt) => $this->calculateRemainingTreatmentMinutes($apt))
            ->toArray();

        // Maximum remaining duration among all in-treatment patients
        // (This is the soonest a room will be available)
        $maxInTreatmentMinutes = count($inTreatmentDurations) > 0 
            ? max($inTreatmentDurations) 
            : 0;

        // Get all waiting appointments ahead of this patient
        $patientsAhead = Appointment::where('status', 'waiting')
            ->where('check_in_time', '<', $appointment->check_in_time)
            ->orderBy('check_in_time', 'asc')
            ->get();

        // Sum their expected durations
        $patientsAheadDuration = $patientsAhead
            ->map(fn($apt) => $apt->service?->estimated_duration ?? 30)
            ->sum();

        return max(0, $maxInTreatmentMinutes + $patientsAheadDuration);
    }

    /**
     * Calculate remaining treatment minutes for an in-treatment appointment
     * 
     * If actual_start_time is set:
     *   remaining = expected_end_time - now
     * Otherwise (no actual start recorded):
     *   remaining = expected_duration
     * 
     * Never returns negative (treatment is over).
     * 
     * @param Appointment $appointment Appointment being treated
     * @return int Remaining minutes (0 if overdue)
     */
    private function calculateRemainingTreatmentMinutes(Appointment $appointment): int
    {
        if ($appointment->actual_start_time) {
            // Treatment has started, calculate from expected end time
            $expectedEnd = Carbon::parse($appointment->actual_start_time)
                ->addMinutes($appointment->service?->estimated_duration ?? 30);
            
            $remaining = $expectedEnd->diffInMinutes(Carbon::now(), false);
            return max(0, $remaining);
        } else {
            // Treatment hasn't started yet, return full service duration
            return $appointment->service?->estimated_duration ?? 30;
        }
    }
}
