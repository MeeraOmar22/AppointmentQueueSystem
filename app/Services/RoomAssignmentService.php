<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Queue;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoomAssignmentService
{
    /**
     * Automatically assign a room to a patient
     * 
     * CRIT-007 FIX: Update room status to 'occupied' when assigned
     * Previously: Room assigned to queue but status never updated
     * Now: Room status updated atomically with queue update
     * 
     * @param Appointment $appointment
     * @param int|null $preferredRoomId - Optional manual room selection
     * @return Room|null
     */
    public function assignRoom(Appointment $appointment, ?int $preferredRoomId = null): ?Room
    {
        $queue = $appointment->queue;
        if (!$queue) {
            return null;
        }

        try {
            return DB::transaction(function () use ($queue, $appointment, $preferredRoomId) {
                // If a specific room is preferred, try to use it
                if ($preferredRoomId) {
                    $room = Room::lockForUpdate()->find($preferredRoomId);
                    if ($room && $room->status === 'available') {
                        // Assign room and update status atomically
                        $queue->update(['room_id' => $room->id]);
                        $room->update(['status' => 'occupied']);  // ← CRITICAL: NOW UPDATED
                        
                        Log::info("Room assigned to patient", [
                            'queue_id' => $queue->id,
                            'appointment_id' => $appointment->id,
                            'room_id' => $room->id,
                        ]);
                        
                        ActivityLogger::log(
                            action: 'room_assigned',
                            modelType: 'Queue',
                            modelId: $queue->id,
                            description: "Room {$room->name} assigned to patient",
                        );
                        
                        return $room;
                    }
                }

                // Otherwise, find the best available room
                $availableRoom = $this->findBestAvailableRoomLocked($appointment->clinic_location);
                
                if ($availableRoom) {
                    $queue->update(['room_id' => $availableRoom->id]);
                    $availableRoom->update(['status' => 'occupied']);  // ← CRITICAL: NOW UPDATED
                    
                    Log::info("Room assigned to patient", [
                        'queue_id' => $queue->id,
                        'appointment_id' => $appointment->id,
                        'room_id' => $availableRoom->id,
                    ]);
                }

                return $availableRoom;
            }, 3);
        } catch (\Exception $e) {
            Log::error("Room assignment failed", [
                'queue_id' => $queue->id,
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Find the best available room for a location
     * - Prioritize empty rooms
     * - Prioritize rooms with patients just checked in (quick turnaround)
     * 
     * @param string $location
     * @return Room|null
     */
    public function findBestAvailableRoom(string $location): ?Room
    {
        $location = strtolower($location);

        // Get all active rooms for this location
        $rooms = Room::where('clinic_location', 'like', '%' . $location . '%')
            ->where('status', 1)
            ->get();

        if ($rooms->isEmpty()) {
            return null;
        }

        // Check each room's current status
        foreach ($rooms as $room) {
            $currentPatient = Queue::where('room_id', $room->id)
                ->whereHas('appointment', function ($query) {
                    $query->whereDate('appointment_date', today());
                })
                ->whereIn('queue_status', ['checked_in', 'in_treatment'])
                ->latest()
                ->first();

            // If room is empty or patient is just checked in, consider it
            if (!$currentPatient || $currentPatient->queue_status === 'called') {
                return $room;
            }
        }

        // If no optimal room found, return first available
        return $rooms->first();
    }

    /**
     * Find the best available room with pessimistic locking
     * Used within transactions to prevent concurrent assignments
     * 
     * @param string $location
     * @return Room|null
     */
    private function findBestAvailableRoomLocked(string $location): ?Room
    {
        $location = strtolower($location);

        // Get all active rooms for this location with locks
        $rooms = Room::where('clinic_location', 'like', '%' . $location . '%')
            ->where('status', 'available')
            ->lockForUpdate()
            ->get();

        if ($rooms->isEmpty()) {
            return null;
        }

        // Check each room's current status
        foreach ($rooms as $room) {
            $currentPatient = Queue::where('room_id', $room->id)
                ->whereHas('appointment', function ($query) {
                    $query->whereDate('appointment_date', today());
                })
                ->whereIn('queue_status', ['checked_in', 'in_treatment'])
                ->latest()
                ->first();

            // If room is empty or patient is just checked in, use it
            if (!$currentPatient || $currentPatient->queue_status === 'called') {
                return $room;
            }
        }

        // If no optimal room found, return first available
        return $rooms->first();
    }

    /**
     * Get room status for a specific location
     * Shows which patients are in which rooms
     * 
     * @param string $location
     * @return array
     */
    public function getRoomStatus(string $location): array
    {
        $location = strtolower($location);
        $rooms = Room::where('clinic_location', 'like', '%' . $location . '%')
            ->get();

        $status = [];
        foreach ($rooms as $room) {
            $currentQueue = Queue::where('room_id', $room->id)
                ->whereHas('appointment', function ($query) {
                    $query->whereDate('appointment_date', today());
                })
                ->whereIn('queue_status', ['checked_in', 'in_treatment'])
                ->latest()
                ->first();

            $status[$room->id] = [
                'room' => $room,
                'current_patient' => $currentQueue?->appointment,
                'status' => $currentQueue?->queue_status ?? 'empty',
                'queue_number' => $currentQueue?->queue_number ?? null,
            ];
        }

        return $status;
    }

    /**
     * Release a room (mark patient as completed in that room)
     * 
     * @param int $roomId
     * @return void
     */
    public function releaseRoom(int $roomId): void
    {
        Queue::where('room_id', $roomId)
            ->whereHas('appointment', function ($query) {
                $query->whereDate('appointment_date', today());
            })
            ->whereIn('queue_status', ['checked_in', 'in_treatment'])
            ->update(['queue_status' => 'completed']);
    }
}
