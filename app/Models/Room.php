<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = [
        'room_number',
        'capacity',
        'status',
        'clinic_location',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    /**
     * Get the queues for this room
     */
    public function queues()
    {
        return $this->hasMany(Queue::class);
    }

    /**
     * Get the current patient in this room (if any)
     */
    public function currentPatient()
    {
        return $this->hasOne(Queue::class)
            ->where('queue_status', 'in_treatment')
            ->latestOfMany();
    }

    /**
     * Check if room is available
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    /**
     * Mark room as occupied
     */
    public function markOccupied(): void
    {
        $this->update(['status' => 'occupied']);
    }

    /**
     * Mark room as available
     */
    public function markAvailable(): void
    {
        $this->update(['status' => 'available']);
    }
}
