<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Queue;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogger;

class CheckInService
{
    /**
     * Process patient check-in
     * FIXED: Now uses pessimistic locking to prevent concurrent duplicate check-ins
     * 
     * @param Appointment $appointment
     * @return Queue|null
     */
    public function checkIn(Appointment $appointment): ?Queue
    {
        return DB::transaction(function () use ($appointment) {
            // FIXED: Use lockForUpdate() on appointment to prevent concurrent check-in attempts
            // This ensures only ONE check-in succeeds for a given appointment
            $lockedAppointment = Appointment::lockForUpdate()->findOrFail($appointment->id);
            
            // Mark appointment as arrived
            $lockedAppointment->markArrived();

            // Get or create queue entry (ONE record per appointment only)
            $queue = $lockedAppointment->queue;
            
            if (!$queue) {
                // Create new queue entry (ONLY on first check-in)
                // FIXED: Use atomic nextNumberForDate() with row locking
                $queue = Queue::create([
                    'appointment_id' => $lockedAppointment->id,
                    'queue_number' => Queue::nextNumberForDate($lockedAppointment->appointment_date),
                    'queue_status' => 'waiting',
                    'check_in_time' => now(),
                ]);
            } else {
                // Queue already exists: validate it's still in correct state
                // Re-validate eligibility even on duplicate (not just logging)
                if (!$this->validateCheckIn($lockedAppointment)['valid']) {
                    return null;
                }
                
                // Duplicate check-in detected: ignore and return existing queue
                ActivityLogger::log(
                    'duplicate_check_in_ignored',
                    'Appointment',
                    $lockedAppointment->id,
                    'Patient ' . $lockedAppointment->patient_name . ' attempted to check in again (ignored)',
                    null,
                    ['queue_number' => $queue->queue_number, 'status' => 'duplicate']
                );
                return $queue;
            }

            // Log the check-in activity
            ActivityLogger::log(
                'checked_in',
                'Appointment',
                $lockedAppointment->id,
                'Patient ' . $lockedAppointment->patient_name . ' checked in',
                null,
                ['queue_number' => $queue->queue_number, 'status' => 'waiting']
            );

            return $queue;
        }, 3); // Retry on deadlock

    }

    /**
     * Validate check-in eligibility
     * 
     * @param Appointment $appointment
     * @return array
     */
    public function validateCheckIn(Appointment $appointment): array
    {
        $errors = [];

        // Check if queue is paused
        $queueSettings = DB::table('queue_settings')->first();
        if ($queueSettings && $queueSettings->is_paused) {
            $errors[] = 'Queue is currently paused. Please wait for queue to resume before checking in.';
        }

        // Check if appointment exists
        if (!$appointment) {
            $errors[] = 'Appointment not found';
            return ['valid' => false, 'errors' => $errors];
        }

        // Check if appointment is for today
        if ($appointment->appointment_date->toDateString() !== now()->toDateString()) {
            $errors[] = 'This appointment is not for today';
        }

        // Check if check-in is outside 30-minute window
        $appointmentTime = $appointment->appointment_date->setTimeFromTimeString($appointment->appointment_time);
        $now = now();
        $minutesUntilAppointment = $now->diffInMinutes($appointmentTime);
        
        // If current time is more than 30 minutes BEFORE appointment, don't allow check-in yet
        if ($now < $appointmentTime && $minutesUntilAppointment > 30) {
            $timeUntil = $appointmentTime->format('g:i A');
            $errors[] = "Check-in opens 30 minutes before your appointment. Your appointment is at $timeUntil. Please check in after " . $appointmentTime->copy()->subMinutes(30)->format('g:i A') . '.';
        }

        // Check if already checked in
        if ($appointment->hasCheckedIn()) {
            $errors[] = 'You have already checked in';
        }

        // Check if appointment is cancelled
        if ($appointment->status->value === 'cancelled') {
            $errors[] = 'This appointment has been cancelled';
        }

        // Check if appointment is marked as no-show
        if ($appointment->status->value === 'no_show') {
            $errors[] = 'This appointment was marked as no-show';
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors,
        ];
    }

    /**
     * Check if patient is late
     * 
     * @param Appointment $appointment
     * @param int $latenessThreshold Minutes after appointment time to mark as late
     * @return bool
     */
    public function isLate(Appointment $appointment, int $latenessThreshold = 15): bool
    {
        $appointmentTime = $appointment->appointment_date->setTimeFromTimeString($appointment->appointment_time);
        $now = now();
        
        return $now->diffInMinutes($appointmentTime) > $latenessThreshold && $now > $appointmentTime;
    }

    /**
     * Handle late check-in
     * 
     * @param Appointment $appointment
     * @return Queue|null
     */
    public function checkInLate(Appointment $appointment): ?Queue
    {
        return DB::transaction(function () use ($appointment) {
            // Mark as late
            $appointment->markLate();

            // Still create queue but note as late
            $queue = $this->checkIn($appointment);

            return $queue;
        });
    }
}
