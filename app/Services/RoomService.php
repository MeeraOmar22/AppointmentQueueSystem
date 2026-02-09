<?php

namespace App\Services;

use App\Models\Room;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\Paginator;

class RoomService
{
    /**
     * Get all rooms for a clinic location
     */
    public function getRoomsByClinic($clinicLocation, $perPage = 15)
    {
        return Room::where('clinic_location', $clinicLocation)
            ->orderBy('room_number', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get room by ID for a specific clinic
     */
    public function getRoomForClinic($roomId, $clinicLocation)
    {
        return Room::where('id', $roomId)
            ->where('clinic_location', $clinicLocation)
            ->firstOrFail();
    }

    /**
     * Create a new room
     */
    public function createRoom($data, $clinicLocation)
    {
        // Check if room already exists
        $exists = Room::where('room_number', $data['room_number'])
            ->where('clinic_location', $clinicLocation)
            ->exists();

        if ($exists) {
            throw new \InvalidArgumentException('Room number already exists for this clinic location.');
        }

        $room = Room::create([
            'room_number' => $data['room_number'],
            'capacity' => $data['capacity'],
            'status' => 'available',
            'is_active' => $data['is_active'] ?? true,
            'clinic_location' => $clinicLocation,
        ]);

        Log::info('Room created', ['room_id' => $room->id, 'room_number' => $room->room_number]);

        return $room;
    }

    /**
     * Update room
     */
    public function updateRoom($roomId, $data, $clinicLocation)
    {
        $room = $this->getRoomForClinic($roomId, $clinicLocation);

        // Check if new room number already exists (if being changed)
        if (isset($data['room_number']) && $data['room_number'] !== $room->room_number) {
            $exists = Room::where('room_number', $data['room_number'])
                ->where('clinic_location', $clinicLocation)
                ->exists();

            if ($exists) {
                throw new \InvalidArgumentException('Room number already exists for this clinic location.');
            }
        }

        $oldData = $room->toArray();
        $room->update($data);

        Log::info('Room updated', ['room_id' => $room->id, 'old' => $oldData, 'new' => $room->toArray()]);

        return $room;
    }

    /**
     * Delete room
     */
    public function deleteRoom($roomId, $clinicLocation)
    {
        $room = $this->getRoomForClinic($roomId, $clinicLocation);
        $roomNumber = $room->room_number;

        $room->delete();

        Log::info('Room deleted', ['room_id' => $roomId, 'room_number' => $roomNumber]);

        return true;
    }

    /**
     * Check if room is available for booking
     */
    public function isRoomAvailable($roomId, $clinicLocation, $startTime = null, $endTime = null)
    {
        $room = $this->getRoomForClinic($roomId, $clinicLocation);

        if (!$room->is_active) {
            return false;
        }

        if (!$startTime || !$endTime) {
            return $room->status === 'available';
        }

        // Check for conflicting bookings
        $conflict = $room->bookings()
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                            ->where('end_time', '>=', $endTime);
                    });
            })
            ->exists();

        return !$conflict;
    }

    /**
     * Get room statistics
     */
    public function getRoomStats($roomId, $clinicLocation)
    {
        $room = $this->getRoomForClinic($roomId, $clinicLocation);

        return [
            'total_bookings' => $room->bookings()->count(),
            'active_bookings' => $room->bookings()->where('status', 'active')->count(),
            'completed_bookings' => $room->bookings()->where('status', 'completed')->count(),
            'cancelled_bookings' => $room->bookings()->where('status', 'cancelled')->count(),
            'status' => $room->status,
            'is_active' => $room->is_active,
            'capacity' => $room->capacity,
        ];
    }
}
