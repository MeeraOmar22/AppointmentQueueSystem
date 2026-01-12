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
     * 
     * @param Appointment $appointment
     * @return Queue|null
     */
    public function checkIn(Appointment $appointment): ?Queue
    {
        return DB::transaction(function () use ($appointment) {
            // Mark appointment as arrived
            $appointment->markArrived();

            // Create or update queue entry
            $queue = $appointment->queue;
            
            if (!$queue) {
                // Create new queue entry
                $queue = Queue::create([
                    'appointment_id' => $appointment->id,
                    'queue_number' => Queue::nextNumberForDate($appointment->appointment_date),
                    'queue_status' => 'waiting',
                    'check_in_time' => now(),
                ]);
            } else {
                // Update existing queue entry
                $queue->update([
                    'check_in_time' => now(),
                    'queue_status' => 'checked_in',
                ]);
            }

            // Log the check-in activity
            ActivityLogger::log(
                'checked_in',
                'Appointment',
                $appointment->id,
                'Patient ' . $appointment->patient_name . ' checked in',
                null,
                ['queue_number' => $queue->queue_number, 'status' => 'checked_in']
            );

            return $queue;
        });
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

        // Check if appointment exists
        if (!$appointment) {
            $errors[] = 'Appointment not found';
            return ['valid' => false, 'errors' => $errors];
        }

        // Check if appointment is for today
        if ($appointment->appointment_date->toDateString() !== now()->toDateString()) {
            $errors[] = 'This appointment is not for today';
        }

        // Check if already checked in
        if ($appointment->hasCheckedIn()) {
            $errors[] = 'Patient has already checked in';
        }

        // Check if appointment is cancelled
        if ($appointment->status === 'cancelled') {
            $errors[] = 'This appointment has been cancelled';
        }

        // Check if appointment is marked as no-show
        if ($appointment->status === 'no_show') {
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
