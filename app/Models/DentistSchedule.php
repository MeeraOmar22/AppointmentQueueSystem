<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DentistSchedule extends Model
{
    protected $fillable = [
        'dentist_id',
        'day_of_week',
        'is_available',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    /**
     * Normalize day_of_week to canonical format: "Monday", "Tuesday", etc.
     */
    public function setDayOfWeekAttribute($value): void
    {
        $normalized = $this->normalizeDayOfWeek($value);
        $this->attributes['day_of_week'] = $normalized;
    }

    /**
     * Normalize day to proper format
     */
    private function normalizeDayOfWeek(string $day): string
    {
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $day_lower = strtolower(trim($day));

        if (in_array($day_lower, $days)) {
            return ucfirst($day_lower);
        }

        return $day; // Return original if unrecognized
    }

    public function dentist()
    {
        return $this->belongsTo(Dentist::class);
    }
}
