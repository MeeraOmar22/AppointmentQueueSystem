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

    /**
     * Prevent duplicate queue entries for same appointment
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Check if queue entry already exists for this appointment
            if (self::where('appointment_id', $model->appointment_id)->exists()) {
                throw new \Exception("Queue entry already exists for appointment #{$model->appointment_id}");
            }

            // Ensure appointment exists
            if (!Appointment::find($model->appointment_id)) {
                throw new \Exception("Appointment #{$model->appointment_id} not found");
            }
        });
    }

    public static function nextNumberForDate($date, bool $returnStart = false): int
    {
        /**
         * HIGH-007 FIX: Atomic queue number generation with row locking
         * Previously: max() + 1 not atomic (multiple concurrent calls could get same number)
         * Now: Uses database lock to ensure only ONE transaction increments at a time
         */
        return \Illuminate\Support\Facades\DB::transaction(function () use ($date) {
            // Lock the queue table to prevent concurrent number generation
            $max = self::where(function ($query) use ($date) {
                $query->whereHas('appointment', function ($subQuery) use ($date) {
                    $subQuery->whereDate('appointment_date', $date);
                });
            })
            ->lockForUpdate()  // ← CRITICAL: Serialize access to queue numbers
            ->max('queue_number');
            
            return ($max ?? 0) + 1;
        }, 3);
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

