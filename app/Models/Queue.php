<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    protected $fillable = [
        'appointment_id',
        'queue_number',
        'queue_status',
        'check_in_time',
        'room_id',
        'dentist_id',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
    ];

    public static function nextNumberForDate($date, bool $returnStart = false): int
    {
        $max = self::whereHas('appointment', function ($query) use ($date) {
            $query->whereDate('appointment_date', $date);
        })->max('queue_number');

        $next = ($max ?? 0) + 1;
        return $returnStart ? $next : $next;
    }

    /**
     * Get the appointment for this queue entry
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get the assigned room
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the assigned dentist
     */
    public function dentist()
    {
        return $this->belongsTo(Dentist::class);
    }

    /**
     * Mark as called (next to be treated)
     */
    public function markCalled(): void
    {
        $this->update(['queue_status' => 'called']);
    }

    /**
     * Mark as in treatment
     */
    public function markInTreatment(): void
    {
        $this->update(['queue_status' => 'in_treatment']);
        if ($this->room) {
            $this->room->markOccupied();
        }
        if ($this->dentist) {
            $this->dentist->markBusy();
        }
    }

    /**
     * Mark as completed
     */
    public function markCompleted(): void
    {
        $this->update(['queue_status' => 'completed']);
        if ($this->room) {
            $this->room->markAvailable();
        }
        if ($this->dentist) {
            $this->dentist->markAvailable();
        }
    }

    /**
     * Check if this entry is waiting
     */
    public function isWaiting(): bool
    {
        return $this->queue_status === 'waiting';
    }

    /**
     * Check if this entry is in treatment
     */
    public function isInTreatment(): bool
    {
        return $this->queue_status === 'in_treatment';
    }
}

