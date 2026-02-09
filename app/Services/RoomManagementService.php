<?php

namespace App\Services;

use App\Models\Room;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Service for managing treatment room operations
 * Handles room status, current patient tracking, availability checks
 * Separates business logic from controller for cleaner architecture
 */
class RoomManagementService
{
    public function __construct(
        private ActivityLogger $activityLogger,
    ) {}

    /**
     * Get all rooms with their current status and patient info
     * @param string $clinicLocation
     * @return Collection of rooms with current patient data
     */
    public function getRoomsWithCurrentPatients(string $clinicLocation = 'seremban'): Collection
    {
        return Room::where('clinic_location', $clinicLocation)
            ->with([
                'queues' => fn($q) => $q->where('queue_status', 'in_treatment')
                    ->with(['appointment.service', 'appointment.dentist'])
            ])
            ->orderBy('room_number')
            ->get()
            ->map(function (Room $room) {
                return [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                    'capacity' => $room->capacity,
                    'status' => $room->status,
                    'is_active' => $room->is_active,
                    'clinic_location' => $room->clinic_location,
                    'current_patient' => $this->getCurrentPatient($room),
                    'is_occupied' => $this->isRoomOccupied($room),
                    'created_at' => $room->created_at,
                ];
            });
    }

    /**
     * Get current patient in a room (if any)
     */
    public function getCurrentPatient(Room $room): ?array
    {
        $queue = $room->queues()
            ->where('queue_status', 'in_treatment')
            ->with(['appointment.service', 'appointment.dentist'])
            ->first();

        if (!$queue) {
            return null;
        }

        return [
            'queue_id' => $queue->id,
            'appointment_id' => $queue->appointment->id,
            'patient_name' => $queue->appointment->patient_name,
            'service' => $queue->appointment->service?->name,
            'dentist_name' => $queue->appointment->dentist?->name,
            'start_time' => $queue->appointment->actual_start_time,
            'queue_number' => $queue->queue_number,
        ];
    }

    /**
     * Check if room is currently occupied
     */
    public function isRoomOccupied(Room $room): bool
    {
        return $room->queues()
            ->where('queue_status', 'in_treatment')
            ->exists();
    }

    /**
     * Get room statistics for a clinic
     */
    public function getRoomStatistics(string $clinicLocation = 'seremban'): array
    {
        $rooms = Room::where('clinic_location', $clinicLocation)->get();
        
        $inTreatmentCount = Queue::whereIn('room_id', $rooms->pluck('id'))
            ->where('queue_status', 'in_treatment')
            ->count();

        return [
            'total_rooms' => $rooms->count(),
            'available_rooms' => $rooms->where('status', 'available')->count(),
            'occupied_rooms' => $rooms->where('status', 'occupied')->count(),
            'active_rooms' => $rooms->where('is_active', true)->count(),
            'inactive_rooms' => $rooms->where('is_active', false)->count(),
            'patients_in_treatment' => $inTreatmentCount,
            'clinic_name' => ucwords(str_replace('_', ' ', $clinicLocation)),
        ];
    }

    /**
     * Create a new room
     */
    public function createRoom(array $data, string $clinicLocation): Room
    {
        $data['clinic_location'] = $clinicLocation;
        $data['status'] = 'available';
        $data['is_active'] = $data['is_active'] ?? true;

        // Validate room doesn't already exist
        $exists = Room::where('room_number', $data['room_number'])
            ->where('clinic_location', $clinicLocation)
            ->exists();

        if ($exists) {
            throw new \InvalidArgumentException(
                'Room number already exists for this clinic location.'
            );
        }

        $room = Room::create($data);

        $this->activityLogger->log(
            'created',
            'Room',
            $room->id,
            "Created new treatment room: {$room->room_number}",
            null,
            $room->toArray()
        );

        return $room;
    }

    /**
     * Update a room
     */
    public function updateRoom(Room $room, array $data): Room
    {
        $oldValues = $room->only(array_keys($data));
        $wasActive = $room->is_active;

        // Check if trying to deactivate room with active treatment
        if (isset($data['is_active']) && !$data['is_active'] && $room->is_active) {
            $activePatients = Queue::where('room_id', $room->id)
                ->where('queue_status', 'in_treatment')
                ->count();

            if ($activePatients > 0) {
                throw new \RuntimeException(
                    "Cannot deactivate {$room->room_number}: Currently treating {$activePatients} patient(s)."
                );
            }
        }

        $room->update($data);
        $room = $room->fresh();

        $this->activityLogger->log(
            'updated',
            'Room',
            $room->id,
            "Updated treatment room: {$room->room_number}",
            $oldValues,
            $room->only(array_keys($data))
        );

        return $room;
    }

    /**
     * Delete a room
     */
    public function deleteRoom(Room $room): void
    {
        // Check for active treatment
        $activeQueue = $room->queues()
            ->where('queue_status', '!=', 'completed')
            ->exists();

        if ($activeQueue) {
            throw new \RuntimeException(
                "Cannot delete room with active treatment. Please complete treatment first."
            );
        }

        $roomNumber = $room->room_number;
        $room->delete();

        $this->activityLogger->log(
            'deleted',
            'Room',
            null,
            "Deleted treatment room: {$roomNumber}",
            ['room_number' => $roomNumber],
            null
        );
    }

    /**
     * Bulk update room status
     */
    public function bulkUpdateStatus(array $roomIds, string $status): int
    {
        $rooms = Room::whereIn('id', $roomIds)->get();
        $updated = 0;

        foreach ($rooms as $room) {
            // Don't change status if room is occupied
            if ($room->status === 'occupied' && $status === 'available') {
                continue;
            }

            if ($room->status !== $status) {
                $room->update(['status' => $status]);
                $updated++;

                $this->activityLogger->log(
                    'updated',
                    'Room',
                    $room->id,
                    "Room status changed to: {$status}",
                    ['status' => $room->getOriginal('status')],
                    ['status' => $status]
                );
            }
        }

        return $updated;
    }

    /**
     * Get available rooms for appointment
     */
    public function getAvailableRooms(string $clinicLocation = 'seremban'): Collection
    {
        // Get rooms that are active and don't have active treatment
        $occupiedRoomIds = Queue::where('queue_status', 'in_treatment')
            ->pluck('room_id')
            ->toArray();

        return Room::where('clinic_location', $clinicLocation)
            ->where('is_active', true)
            ->whereNotIn('id', $occupiedRoomIds)
            ->orderBy('room_number')
            ->get();
    }

    /**
     * Mark room as occupied
     */
    public function markOccupied(Room $room): void
    {
        $room->update(['status' => 'occupied']);

        $this->activityLogger->log(
            'updated',
            'Room',
            $room->id,
            "Room marked as occupied: {$room->room_number}",
            ['status' => 'available'],
            ['status' => 'occupied']
        );
    }

    /**
     * Mark room as available
     */
    public function markAvailable(Room $room): void
    {
        $room->update(['status' => 'available']);

        $this->activityLogger->log(
            'updated',
            'Room',
            $room->id,
            "Room marked as available: {$room->room_number}",
            ['status' => 'occupied'],
            ['status' => 'available']
        );
    }
}
