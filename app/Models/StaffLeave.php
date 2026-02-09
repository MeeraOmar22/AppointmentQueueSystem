<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * StaffLeave - Manage leave for staff members (receptionists, assistants, etc.)
 * 
 * Tracks when staff members are unavailable.
 * Staff with leave may impact:
 * - Appointment check-in operations
 * - Queue management
 * - Patient communication
 */
class StaffLeave extends Model
{
    protected $fillable = [
        'user_id',  // Reference to User model (staff member)
        'start_date',
        'end_date',
        'reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Boot the model.
     * Add validation to ensure end_date >= start_date
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if ($model->end_date < $model->start_date) {
                throw new \Exception('End date cannot be before start date');
            }
        });

        static::updating(function ($model) {
            if ($model->end_date < $model->start_date) {
                throw new \Exception('End date cannot be before start date');
            }
        });
    }

    /**
     * Check if staff member is on leave on a specific date
     */
    public function isOnLeaveOnDate($date): bool
    {
        return $date->between($this->start_date, $this->end_date);
    }
}
