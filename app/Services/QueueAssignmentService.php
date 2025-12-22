<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\Room;
use App\Models\Dentist;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class QueueAssignmentService
{
    /**
     * Automatically assign next waiting patient to available room/dentist
     * 
     * @param string $clinicLocation
     * @return Queue|null
     */
    public function assignNextPatient(string $clinicLocation = 'seremban'): ?Queue
    {
        return DB::transaction(function () use ($clinicLocation) {
            // Get next waiting patient (earliest check-in)
            $queue = Queue::where('queue_status', 'waiting')
                ->whereHas('appointment', function ($query) use ($clinicLocation) {
                    $query->whereDate('appointment_date', Carbon::today())
                        ->where('clinic_location', $clinicLocation);
                })
                ->orderBy('check_in_time')
                ->first();

            if (!$queue) {
                return null;
            }

            // Find available room
            $room = $this->findAvailableRoom($clinicLocation);
            if (!$room) {
                return null;
            }

            // Find available dentist
            $dentist = $this->findAvailableDentist($clinicLocation, $queue->appointment);
            if (!$dentist) {
                return null;
            }

            // Assign to queue
            $queue->update([
                'queue_status' => 'called',
                'room_id' => $room->id,
                'dentist_id' => $dentist->id,
            ]);

            // Mark appointment as in queue
            $queue->appointment->update(['status' => 'in_queue']);

            // Log assignment
            activity()
                ->performedOn($queue->appointment)
                ->event('queue_assigned')
                ->log('Assigned to Room ' . $room->room_number . ' with Dr. ' . $dentist->name);

            return $queue;
        });
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

            activity()
                ->performedOn($queue->appointment)
                ->event('treatment_started')
                ->log('Treatment started in Room ' . $queue->room?->room_number);
        });
    }

    /**
     * Complete treatment for a queue entry
     * 
     * @param Queue $queue
     * @return void
     */
    public function completeTreatment(Queue $queue): void
    {
        DB::transaction(function () use ($queue) {
            $queue->markCompleted();
            $queue->appointment->markCompleted();

            activity()
                ->performedOn($queue->appointment)
                ->event('treatment_completed')
                ->log('Treatment completed');

            // Try to assign next patient
            $this->assignNextPatient($queue->appointment->clinic_location);
        });
    }

    /**
     * Find first available room
     * 
     * @param string $clinicLocation
     * @return Room|null
     */
    private function findAvailableRoom(string $clinicLocation = 'seremban'): ?Room
    {
        return Room::where('clinic_location', $clinicLocation)
            ->where('status', 'available')
            ->orderBy('room_number')
            ->first();
    }

    /**
     * Find available dentist
     * Prefers the assigned dentist if available, otherwise picks any available
     * 
     * @param string $clinicLocation
     * @param Appointment $appointment
     * @return Dentist|null
     */
    private function findAvailableDentist(string $clinicLocation, Appointment $appointment): ?Dentist
    {
        // First try the assigned dentist
        if ($appointment->dentist && $appointment->dentist->isAvailable()) {
            return $appointment->dentist;
        }

        // Otherwise pick any available dentist
        return Dentist::where('status', 'available')
            ->orderBy('name')
            ->first();
    }

    /**
     * Get estimated wait time for a patient in queue
     * 
     * @param Queue $queue
     * @return int Minutes
     */
    public function getEstimatedWaitTime(Queue $queue): int
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
     * Get estimated treatment duration for an appointment
     * 
     * @param Queue $queue
     * @return int Minutes
     */
    private function getEstimatedTreatmentDuration(Queue $queue): int
    {
        if ($queue->appointment && $queue->appointment->service) {
            return max((int) ($queue->appointment->service->estimated_duration ?? 0), 15);
        }

        return 15; // Default 15 minutes
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

            'available_dentists' => Dentist::where('status', 'available')->count(),
        ];
    }
}
