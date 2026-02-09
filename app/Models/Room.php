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
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'is_active' => 'boolean',
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

    /**
     * Check if room is active (can receive assignments)
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Scope query to only active rooms
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Activate room for scheduling
     */
    public function activate(): void
    {
        $this->update(['is_active' => true]);
    }

    /**
     * Deactivate room from scheduling
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }
}
