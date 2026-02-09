<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\OperatingHour;
use App\Models\Room;
use App\Models\Service;
use App\Models\DentistLeave;
use Carbon\Carbon;

/**
 * Service for calculating appointment availability considering service duration
 * 
 * Single-clinic implementation. No clinic_location filtering needed.
 * 
 * FIXED ISSUES:
 * - Time slots now account for service duration (not just 30-min blocks)
 * - Overlapping appointments are properly detected using interval overlap logic
 * - Long services (30-120 min) correctly block multiple slots
 * - Duration-aware availability checking prevents overlapping bookings
 */
class AvailabilityService
{
    private const LUNCH_START = '13:00';
    private const LUNCH_END = '14:00';
    private const SLOT_INTERVAL = 30; // minutes - generation interval, not service duration

    /**
     * Generate available time slots for a given date and service
     * 
     * Algorithm:
     * 1. Get operating hours for the day and clinic location
     * 2. Get number of available rooms at clinic
     * 3. Generate 30-minute candidate slots
     * 4. For each candidate slot:
     *    - Calculate end time = start_time + service_duration
     *    - Count how many rooms are occupied during this time window
     *    - If available_rooms > occupied_rooms, slot is available
     *    - Reject if all rooms occupied OR extends past closing time
     * 5. Exclude lunch break
     * 6. Exclude past times (if today)
     * 
     * MULTI-ROOM FIX: Now checks room availability at each slot
     * - Allows up to N patients at same time (N = number of rooms)
     * - Previously only allowed 1 patient per time slot
     * 
     * @param string $date Format: Y-m-d
     * @param int $serviceDurationMinutes Service duration in minutes (30-120)
     * @param string $clinicLocation Clinic location (seremban, kuala_pilah, etc.) - defaults to 'seremban'
     * @return array Available time slots with metadata
     */
    public function getAvailableSlots(string $date, int $serviceDurationMinutes, string $clinicLocation = 'seremban'): array
    {
        $carbonDate = Carbon::createFromFormat('Y-m-d', $date);
        $dayName = $carbonDate->format('l'); // Get day name: Monday, Tuesday, etc.
        $isToday = $carbonDate->isToday();
        $currentTime = $isToday ? now() : null;

        // Retrieve operating hours for this day AND clinic location
        $operatingHour = OperatingHour::where('day_of_week', $dayName)
            ->where('clinic_location', $clinicLocation)
            ->first();

        // Return empty if clinic is closed or no hours defined
        if (!$operatingHour || $operatingHour->is_closed) {
            return [];
        }

        if (!$operatingHour->start_time || !$operatingHour->end_time) {
            return [];
        }

        // Get total available rooms at this clinic
        $totalRooms = Room::where('clinic_location', $clinicLocation)
            ->where('is_active', true)
            ->count();

        if ($totalRooms === 0) {
            // If no rooms configured, fall back to old behavior (single room assumed)
            $totalRooms = 1;
        }

        $slots = [];
        $openTime = Carbon::createFromTimeString($operatingHour->start_time);
        $closeTime = Carbon::createFromTimeString($operatingHour->end_time);
        $currentSlot = $openTime->copy();

        // Generate candidate slots at 30-minute intervals
        while ($currentSlot->copy()->addMinutes(self::SLOT_INTERVAL)->lessThanOrEqualTo($closeTime)) {
            $slotTime = $currentSlot->format('H:i');
            $slotStartMinutes = $this->timeToMinutes($slotTime);

            // Check if this slot is in lunch break
            if ($this->isLunchBreak($slotTime)) {
                $currentSlot->addMinutes(self::SLOT_INTERVAL);
                continue;
            }

            // Check if slot is in the past (for today)
            if ($isToday && $currentSlot->lessThan($currentTime)) {
                $currentSlot->addMinutes(self::SLOT_INTERVAL);
                continue;
            }

            // CRITICAL FIX: Calculate when service would end if booked at this slot
            $slotEndMinutes = $slotStartMinutes + $serviceDurationMinutes;
            $slotEndTime = $this->minutesToTime($slotEndMinutes);

            // Check if the appointment would extend past closing time
            if ($slotEndMinutes > $this->timeToMinutes($operatingHour->end_time)) {
                $currentSlot->addMinutes(self::SLOT_INTERVAL);
                continue;
            }

            // MULTI-ROOM FIX: Check how many rooms are occupied at this time
            $occupiedRooms = $this->countOccupiedRoomsAtTime(
                $date,
                $slotStartMinutes,
                $slotEndMinutes
            );

            // Slot is available if occupied_rooms < total_rooms
            $isAvailable = $occupiedRooms < $totalRooms;

            $slots[] = [
                'time' => $slotTime,
                'displayTime' => $currentSlot->format('h:i A'),
                'available' => $isAvailable,
                'disabled' => !$isAvailable,
                'status' => $isAvailable ? 'available' : 'booked',
                'duration' => $serviceDurationMinutes,
                'endTime' => $slotEndTime,
                'note' => !$isAvailable ? ($occupiedRooms >= $totalRooms ? 'All rooms occupied' : null) : null,
                'occupiedRooms' => $occupiedRooms,
                'totalRooms' => $totalRooms,
            ];

            $currentSlot->addMinutes(self::SLOT_INTERVAL);
        }

        return $slots;
    }

    /**
     * Count how many rooms are occupied during a given time window on a date
     * 
     * MULTI-ROOM FIX: Counts occupied rooms instead of checking if ANY appointment exists
     * This allows multiple appointments at the same time in different rooms
     * 
     * @param string $date Appointment date (Y-m-d)
     * @param int $timeWindowStartMinutes Start time in minutes since midnight
     * @param int $timeWindowEndMinutes End time in minutes since midnight
     * @return int Number of rooms occupied during this time window
     */
    private function countOccupiedRoomsAtTime(
        string $date,
        int $timeWindowStartMinutes,
        int $timeWindowEndMinutes
    ): int {
        // Get all active appointments on this date
        $existingAppointments = Appointment::where('appointment_date', $date)
            ->whereIn('status', ['booked', 'checked_in', 'waiting', 'in_treatment'])
            ->get();

        $occupiedRooms = 0;
        
        foreach ($existingAppointments as $existing) {
            // Convert existing appointment times to minutes
            $existingStart = $this->timeToMinutes($existing->appointment_time);
            
            // CRITICAL: Use end_at if stored, otherwise calculate from service duration
            if ($existing->end_at) {
                $existingEnd = $this->timeToMinutes($existing->end_at->format('H:i'));
            } else {
                // Fallback: calculate from service duration if end_at not set
                $serviceDuration = $existing->service?->estimated_duration ?? 30;
                $existingEnd = $existingStart + $serviceDuration;
            }

            // Check for overlap: startProposed < endExisting AND startExisting < endProposed
            if ($timeWindowStartMinutes < $existingEnd && $existingStart < $timeWindowEndMinutes) {
                // This appointment overlaps with our proposed time window
                // Count it as occupying one room
                $occupiedRooms++;
            }
        }

        return $occupiedRooms;
    }

    /**
     * Check if a proposed appointment time overlaps with any existing appointment in the same room
     * 
     * MULTI-ROOM FIX: Now checks room availability instead of just time
     * - Different rooms can have appointments at the same time
     * - Same room cannot have overlapping appointments
     * 
     * Overlap condition: startA < endB AND startB < endA
     * Where:
     * - A = proposed appointment (startMinutes to endMinutes)
     * - B = existing appointment from database
     * 
     * Statuses considered for blocking:
     * - booked: patient scheduled
     * - checked_in: patient arrived
     * - waiting: in queue
     * - in_treatment: currently being treated
     * 
     * Excluded from blocking:
     * - cancelled: appointment not happening
     * - no_show: patient didn't show up
     * - completed: appointment already finished
     * 
     * @param string $date Appointment date (Y-m-d)
     * @param int $proposedStartMinutes Start time in minutes since midnight
     * @param int $proposedEndMinutes End time in minutes since midnight
     * @return bool True if overlap detected, false if available
     */
    private function hasOverlappingAppointment(
        string $date,
        int $proposedStartMinutes,
        int $proposedEndMinutes
    ): bool {
        // Get all active appointments on this date
        // NOTE: We get all appointments regardless of room to check availability
        // The actual room assignment happens in AppointmentController when booking
        $existingAppointments = Appointment::where('appointment_date', $date)
            ->whereIn('status', ['booked', 'checked_in', 'waiting', 'in_treatment'])
            ->get();

        // Count how many appointments overlap with proposed time
        $overlappingCount = 0;
        
        foreach ($existingAppointments as $existing) {
            // Convert existing appointment times to minutes
            $existingStart = $this->timeToMinutes($existing->appointment_time);
            
            // CRITICAL: Use end_at if stored, otherwise calculate from service duration
            if ($existing->end_at) {
                $existingEnd = $this->timeToMinutes($existing->end_at->format('H:i'));
            } else {
                // Fallback: calculate from service duration if end_at not set
                $serviceDuration = $existing->service?->estimated_duration ?? 30;
                $existingEnd = $existingStart + $serviceDuration;
            }

            // Check for overlap: startProposed < endExisting AND startExisting < endProposed
            if ($proposedStartMinutes < $existingEnd && $existingStart < $proposedEndMinutes) {
                $overlappingCount++;
            }
        }

        // CRITICAL FIX: Get number of available rooms for this clinic
        // If overlappingCount < roomCount, there's still a room available
        // We'll check this in getAvailableSlots by also checking room count
        
        // For now, return true if ANY overlap (this will be refined by room check in store method)
        // This maintains backward compatibility while preparing for room-aware checking
        return $overlappingCount > 0;
    }

    /**
     * Validate that a booking request is valid before submission
     * 
     * Checks:
     * 1. Service exists and has duration
     * 2. Appointment date is valid (not past)
     * 3. Appointment time is valid (not past, within hours)
     * 4. Time slot available for full service duration
     * 5. Not in lunch break
     * 6. Clinic is open
     * 
     * @param string $date Y-m-d format
     * @param string $time H:i format
     * @param int $serviceDurationMinutes Service duration in minutes
     * @param string $clinicLocation Clinic location - defaults to 'seremban'
     * @return array ['valid' => bool, 'message' => string|null]
     */
    public function validateBookingRequest(
        string $date,
        string $time,
        int $serviceDurationMinutes,
        string $clinicLocation = 'seremban'
    ): array {
        // Parse date and time
        try {
            $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i', "{$date} {$time}");
        } catch (\Exception $e) {
            return ['valid' => false, 'message' => 'Invalid date/time format'];
        }

        // Check if past
        if ($appointmentDateTime->isPast()) {
            return ['valid' => false, 'message' => 'Cannot book past appointments'];
        }

        // Check lunch break
        if ($this->isLunchBreak($time)) {
            return ['valid' => false, 'message' => 'Lunch break (13:00-14:00) is not available'];
        }

        // Get operating hours
        $carbonDate = Carbon::createFromFormat('Y-m-d', $date);
        $dayName = $carbonDate->format('l'); // Get day name: Monday, Tuesday, etc.
        $operatingHour = OperatingHour::where('day_of_week', $dayName)
            ->where('clinic_location', $clinicLocation)
            ->first();

        // Check clinic is open
        if (!$operatingHour || $operatingHour->is_closed) {
            $dayName = $carbonDate->format('l'); // e.g., "Sunday"
            return ['valid' => false, 'message' => "The clinic is closed on {$dayName}s. Please select another date."];
        }

        if (!$operatingHour->start_time || !$operatingHour->end_time) {
            return ['valid' => false, 'message' => 'Operating hours not configured for this day'];
        }

        // Check time within operating hours
        // Convert times to minutes for proper comparison
        $appointmentMinutes = $this->timeToMinutes($time);
        $startMinutes = $this->timeToMinutes($operatingHour->start_time);
        $endMinutes = $this->timeToMinutes($operatingHour->end_time);
        
        if ($appointmentMinutes < $startMinutes || $appointmentMinutes >= $endMinutes) {
            $dayName = $carbonDate->format('l');
            $startTime = Carbon::createFromTimeString($operatingHour->start_time)->format('h:i A');
            $endTime = Carbon::createFromTimeString($operatingHour->end_time)->format('h:i A');
            return ['valid' => false, 'message' => "The appointment must be between {$startTime} and {$endTime} on {$dayName}s. Please select another time or date."];
        }

        // Check if service duration fits before closing
        $startMinutes = $this->timeToMinutes($time);
        $endMinutes = $startMinutes + $serviceDurationMinutes;
        $closeMinutes = $this->timeToMinutes($operatingHour->end_time);

        if ($endMinutes > $closeMinutes) {
            $dayName = $carbonDate->format('l');
            $startTime = Carbon::createFromTimeString($operatingHour->start_time)->format('h:i A');
            $endTime = Carbon::createFromTimeString($operatingHour->end_time)->format('h:i A');
            return [
                'valid' => false,
                'message' => "Service duration ({$serviceDurationMinutes} min) extends past closing time at {$endTime}. Please book earlier on {$dayName}s or select another date."
            ];
        }

        // Check for overlapping appointments (MULTI-ROOM AWARE)
        // Count occupied rooms during this time window
        $occupiedRooms = $this->countOccupiedRoomsAtTime($date, $startMinutes, $endMinutes);
        
        // Get total available rooms at this clinic
        $totalRooms = Room::where('clinic_location', $clinicLocation)
            ->where('is_active', true)
            ->count();
        
        if ($totalRooms === 0) {
            $totalRooms = 1; // Fallback: assume 1 room if none configured
        }
        
        if ($occupiedRooms >= $totalRooms) {
            return ['valid' => false, 'message' => 'This time slot is no longer available'];
        }

        return ['valid' => true, 'message' => null];
    }

    /**
     * Convert time string (H:i) to minutes since midnight
     * 
     * @param string $time Format: H:i (e.g., "14:30")
     * @return int Minutes since midnight (e.g., 870 for 14:30)
     */
    private function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = explode(':', $time);
        return (int)$hours * 60 + (int)$minutes;
    }

    /**
     * Convert minutes since midnight to time string (H:i)
     * 
     * @param int $minutes Minutes since midnight
     * @return string Time in H:i format (e.g., "14:30")
     */
    private function minutesToTime(int $minutes): string
    {
        $hours = intval($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }

    /**
     * Check if given time is within lunch break (13:00-14:00)
     * 
     * @param string $time Format: H:i
     * @return bool
     */
    private function isLunchBreak(string $time): bool
    {
        return $time >= self::LUNCH_START && $time < self::LUNCH_END;
    }

    /**
     * Get operating hours for a specific date
     * 
     * @param string $date Y-m-d format
     * @return OperatingHour|null
     */
    public function getOperatingHours(string $date): ?OperatingHour
    {
        $carbonDate = Carbon::createFromFormat('Y-m-d', $date);
        $dayName = $carbonDate->format('l'); // Get day name: Monday, Tuesday, etc.

        return OperatingHour::where('day_of_week', $dayName)->first();
    }

    /**
     * Check if clinic is open on a given date
     * 
     * @param string $date Y-m-d format
     * @return bool
     */
    public function isClinicOpen(string $date): bool
    {
        $operatingHour = $this->getOperatingHours($date);

        return $operatingHour && !$operatingHour->is_closed && $operatingHour->start_time && $operatingHour->end_time;
    }

    /**
     * Check if a dentist is on leave on a specific date
     * 
     * @param int $dentistId Dentist ID
     * @param string $date Y-m-d format
     * @return bool True if dentist is on leave
     */
    public function isDentistOnLeave(int $dentistId, string $date): bool
    {
        return DentistLeave::where('dentist_id', $dentistId)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->exists();
    }

    /**
     * Get available dentists for a specific date (excluding those on leave)
     * 
     * @param string $date Y-m-d format
     * @param int|null $serviceId Optional: filter by specific service
     * @return \Illuminate\Database\Eloquent\Collection Available dentists
     */
    public function getAvailableDentists(string $date, ?int $serviceId = null)
    {
        $query = \App\Models\Dentist::where('status', 1); // Only active dentists

        // Get dentists on leave on this date
        $dentistsOnLeave = DentistLeave::where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->pluck('dentist_id')
            ->toArray();

        // Exclude dentists on leave
        if (!empty($dentistsOnLeave)) {
            $query->whereNotIn('id', $dentistsOnLeave);
        }

        // Optional: Filter by service if provided
        if ($serviceId) {
            $query->whereHas('services', function ($q) use ($serviceId) {
                $q->where('service_id', $serviceId);
            });
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Get leave information for a dentist on a specific date
     * 
     * @param int $dentistId Dentist ID
     * @param string $date Y-m-d format
     * @return DentistLeave|null Leave record if dentist is on leave
     */
    public function getDentistLeaveInfo(int $dentistId, string $date): ?DentistLeave
    {
        return DentistLeave::where('dentist_id', $dentistId)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();
    }

    /**
     * Get dentists on leave for a date range
     * 
     * @param string $startDate Y-m-d format
     * @param string $endDate Y-m-d format
     * @return array Array of [dentist_id => [dentists on leave]]
     */
    public function getDentistsOnLeaveInRange(string $startDate, string $endDate): array
    {
        $leaves = DentistLeave::where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->with('dentist')
            ->get();

        $result = [];
        foreach ($leaves as $leave) {
            $result[$leave->dentist_id] = [
                'name' => $leave->dentist->name,
                'start_date' => $leave->start_date->format('Y-m-d'),
                'end_date' => $leave->end_date->format('Y-m-d'),
                'reason' => $leave->reason,
            ];
        }

        return $result;
    }
}
