<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Queue;
use App\Models\Room;
use Carbon\Carbon;

/**
 * CONSOLIDATED ETA Calculation Service
 * 
 * ✅ Single source of truth for all wait time calculations
 * ✅ Accounts for multiple concurrent treatment rooms
 * ✅ Accurate formula: ETA = (patients ahead / available rooms) × service_duration
 * ✅ Integrates with both staff and public interfaces
 * 
 * USAGE:
 *   $eTA = new EstimatedWaitTimeService();
 *   $minutes = $eta->getETAForAppointment($appointment);
 */
class EstimatedWaitTimeService
{
    /**
     * Get estimated wait time for an appointment
     * 
     * Returns different values based on appointment status:
     * - WAITING: Calculates based on queue position and available rooms
     * - IN_TREATMENT: Returns 0 (treatment is happening now)
     * - BOOKED/CONFIRMED: Returns null (ETA only meaningful after check-in)
     * - OTHER: Returns null
     * 
     * @param Appointment $appointment
     * @return int|null Estimated wait time in minutes, or null if not applicable
     */
    public function getETAForAppointment(Appointment $appointment): ?int
    {
        $status = $appointment->status->value;
        $serviceDuration = max((int) ($appointment->service->estimated_duration ?? 0), 15);
        $clinicLocation = $appointment->clinic_location;

        $result = match ($status) {
            Appointment::STATE_WAITING => $this->calculateWaitingETA($appointment, $serviceDuration, $clinicLocation),
            Appointment::STATE_IN_TREATMENT => 0,
            // ✅ BOOKED/CONFIRMED: Return null - ETA only meaningful after check-in
            // Showing speculative ETA for unchecked-in patients is misleading
            Appointment::STATE_BOOKED => null,
            Appointment::STATE_CONFIRMED => null,
            'booked' => null,
            default => null,
        };

        // DEBUG: Log the result
        \Log::debug('ETA Result', [
            'appointment_id' => $appointment->id,
            'status' => $status,
            'clinic_location' => $clinicLocation,
            'result' => $result
        ]);

        return $result;
    }

    /**
     * Calculate ETA for a patient currently WAITING in queue
     * 
     * Formula: ETA = ⌈patients_ahead / available_rooms⌉ × service_duration
     * 
     * This properly accounts for:
     * ✅ Multiple patients being treated concurrently in different rooms
     * ✅ Patients already being treated (room occupancy)
     * ✅ Dynamic room availability
     * ✅ Service duration variations per patient
     * 
     * @param Appointment $appointment
     * @param int $serviceDuration
     * @param string $clinicLocation
     * @return int Estimated wait time in minutes (min 0)
     */
    private function calculateWaitingETA(Appointment $appointment, int $serviceDuration, string $clinicLocation): int
    {
        $queue = $appointment->queue;
        if (!$queue) {
            return 0; // No queue assigned yet
        }

        // Count ONLY WAITING patients ahead (not in treatment!)
        // Patients in treatment don't block queue movement - rooms are being used by them
        $patientsAheadWaiting = Queue::whereHas('appointment', function ($q) use ($appointment, $clinicLocation) {
            $q->whereDate('appointment_date', $appointment->appointment_date)
              ->where('clinic_location', $clinicLocation)
              ->where('status', '!=', Appointment::STATE_COMPLETED)
              ->where('status', '!=', Appointment::STATE_FEEDBACK_SENT);
        })
            ->where('queue_number', '<', $queue->queue_number)
            ->where('queue_status', 'waiting')  // ONLY waiting, not in_treatment
            ->count();

        // Count currently occupied rooms
        $occupiedRooms = Queue::whereHas('appointment', function ($q) use ($appointment, $clinicLocation) {
            $q->whereDate('appointment_date', $appointment->appointment_date)
              ->where('clinic_location', $clinicLocation)
              ->where('status', '!=', Appointment::STATE_COMPLETED)
              ->where('status', '!=', Appointment::STATE_FEEDBACK_SENT);
        })
            ->where('queue_status', 'in_treatment')
            ->distinct('room_id')
            ->count('room_id');

        // Get total active rooms
        $totalRooms = Room::where('clinic_location', $clinicLocation)
            ->where('is_active', true)
            ->count();

        if ($totalRooms === 0) {
            return 0; // No rooms available
        }

        // Calculate available rooms
        $availableRooms = max(0, $totalRooms - $occupiedRooms);

        // DEBUG: Log calculation details
        \Log::debug('calculateWaitingETA', [
            'appointment' => $appointment->id,
            'queue_number' => $queue->queue_number,
            'patientsAheadWaiting' => $patientsAheadWaiting,
            'occupiedRooms' => $occupiedRooms,
            'totalRooms' => $totalRooms,
            'availableRooms' => $availableRooms,
            'serviceDuration' => $serviceDuration
        ]);

        // If available room, check if patient can use it
        if ($availableRooms > 0) {
            // If no waiting patients ahead, this patient can go directly to room
            if ($patientsAheadWaiting === 0) {
                \Log::debug('Room available and no waiting ahead, returning 0');
                return 0;
            }
            
            // There are waiting patients ahead - must wait for them
            // ETA = ⌈patients_ahead_waiting / available_rooms⌉ × service_duration
            $result = max(0, ceil($patientsAheadWaiting / $availableRooms) * $serviceDuration);
            \Log::debug('Waiting ahead, using formula', ['result' => $result]);
            return $result;
        }

        // No available rooms - must wait for a room to become free
        // Return max remaining treatment time + this patient's service duration
        $maxRemainingTime = $this->getMaxRemainingTreatmentTime($appointment->appointment_date, $clinicLocation);
        $result = $maxRemainingTime + $serviceDuration;
        \Log::debug('No rooms available, returning max remaining + service duration', ['result' => $result]);
        return $result;
    }

    /**
     * Calculate ETA for a BOOKED appointment
     * 
     * Estimates wait time based on current queue activity for that appointment date
     * 
     * @param Appointment $appointment
     * @param int $serviceDuration
     * @param string $clinicLocation
     * @return int Estimated wait time in minutes
     */
    private function calculateBookedETA(Appointment $appointment, int $serviceDuration, string $clinicLocation): int
    {
        // Count all patients currently waiting or in treatment
        $patientsInQueue = Queue::whereHas('appointment', function ($q) use ($appointment, $clinicLocation) {
            $q->whereDate('appointment_date', $appointment->appointment_date)
              ->where('clinic_location', $clinicLocation)
              ->where('status', '!=', Appointment::STATE_COMPLETED)
              ->where('status', '!=', Appointment::STATE_FEEDBACK_SENT);
        })
            ->whereIn('queue_status', ['waiting', 'in_treatment'])
            ->count();

        if ($patientsInQueue === 0) {
            return 0;
        }

        // Get total active rooms
        $totalRooms = Room::where('clinic_location', $clinicLocation)
            ->where('is_active', true)
            ->count();

        if ($totalRooms === 0) {
            return 0;
        }

        return max(0, ceil($patientsInQueue / $totalRooms) * $serviceDuration);
    }

    /**
     * Get maximum remaining treatment time among all in-treatment patients
     * 
     * This tells us when the soonest room will become available
     * 
     * @param string $appointmentDate
     * @param string $clinicLocation
     * @return int Minutes until soonest room is available
     */
    private function getMaxRemainingTreatmentTime(string $appointmentDate, string $clinicLocation): int
    {
        $inTreatment = Appointment::where('status', Appointment::STATE_IN_TREATMENT)
            ->whereDate('appointment_date', $appointmentDate)
            ->where('clinic_location', $clinicLocation)
            ->get();

        if ($inTreatment->isEmpty()) {
            return 0;
        }

        $maxRemaining = 0;
        foreach ($inTreatment as $appointment) {
            $remaining = $this->calculateRemainingTreatmentMinutes($appointment);
            $maxRemaining = max($maxRemaining, $remaining);
        }

        return $maxRemaining;
    }

    /**
     * Calculate remaining treatment minutes for a patient
     * 
     * If actual_start_time exists:
     *   remaining = expected_end_time - now
     * Otherwise:
     *   remaining = service_duration (not started yet)
     * 
     * @param Appointment $appointment
     * @return int Remaining minutes (0 if overdue)
     */
    private function calculateRemainingTreatmentMinutes(Appointment $appointment): int
    {
        $serviceDuration = $appointment->service?->estimated_duration ?? 30;

        if ($appointment->actual_start_time) {
            // Treatment started - calculate from expected end time
            $expectedEnd = Carbon::parse($appointment->actual_start_time)
                ->addMinutes($serviceDuration);
            
            // Use absolute value to get the difference in minutes
            // Then negate if end time is in the past (treatment overdue)
            $remaining = $expectedEnd->diffInMinutes(Carbon::now(), true);
            
            // If expected end time is in the future, it's positive remaining
            // If in the past, treatment is overdue (return 0)
            if ($expectedEnd->isFuture()) {
                return $remaining;
            }
            return 0;
        }

        // Treatment hasn't started - return full service duration
        return $serviceDuration;
    }
}
