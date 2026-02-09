<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OperatingHour extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $fillable = [
        'day_of_week',
        'clinic_location',
        'session_label',
        'start_time',
        'end_time',
        'is_closed',
    ];

    protected $casts = [
        'is_closed' => 'boolean',
    ];

    /**
     * Scope: Get hours for specific day
     */
    public function scopeForDay($query, $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    /**
     * Scope: Get hours for specific clinic location
     */
    public function scopeForClinicLocation($query, $clinicLocation)
    {
        return $query->where('clinic_location', $clinicLocation);
    }

    /**
     * Scope: Get open sessions only (not closed)
     */
    public function scopeOpen($query)
    {
        return $query->where('is_closed', false);
    }

    /**
     * Scope: Get hours for a specific day and clinic location
     */
    public function scopeForDayAndLocation($query, $dayOfWeek, $clinicLocation)
    {
        return $query->where('day_of_week', $dayOfWeek)
                     ->where('clinic_location', $clinicLocation);
    }

    /**
     * Get today's operating hours for a specific clinic location
     * @param string $clinicLocation - Clinic location identifier (e.g., 'seremban')
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getTodaysHours($clinicLocation = null)
    {
        $today = now()->format('l'); // Returns: Monday, Tuesday, etc.
        $query = self::where('day_of_week', $today);
        
        if ($clinicLocation) {
            $query->where('clinic_location', $clinicLocation);
        }
        
        return $query->get();
    }

    /**
     * Check if clinic is open today for a specific location
     * @param string $clinicLocation - Clinic location identifier
     * @return bool
     */
    public static function isOpenToday($clinicLocation = null)
    {
        $todaysHours = self::getTodaysHours($clinicLocation);
        
        // If no hours found, clinic is closed
        if ($todaysHours->isEmpty()) {
            return false;
        }
        
        // Check if any session is not marked as closed
        return $todaysHours->contains(fn($hour) => !$hour->is_closed);
    }
}
