<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dentist extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'specialization',
        'email',
        'phone',
        'photo',
        'bio',
        'years_of_experience',
        'twitter_url',
        'facebook_url',
        'linkedin_url',
        'instagram_url',
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function schedules()
    {
        return $this->hasMany(DentistSchedule::class);
    }

    public function leaves()
    {
        return $this->hasMany(DentistLeave::class);
    }

    /**
     * Get current queue entries for this dentist
     */
    public function currentQueue()
    {
        return $this->hasMany(Queue::class)
            ->where('queue_status', '!=', 'completed');
    }

    /**
     * Check if dentist is available
     */
    public function isAvailable(): bool
    {
        return (bool) $this->status;
    }

    /**
     * Mark dentist as busy
     */
    public function markBusy(): void
    {
        $this->update(['status' => false]);
    }

    /**
     * Mark dentist as available
     */
    public function markAvailable(): void
    {
        $this->update(['status' => true]);
    }
}

