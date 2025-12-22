<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Appointment extends Model
{
    protected $fillable = [
        'patient_name',
        'patient_phone',
        'patient_email',
        'clinic_location',
        'service_id',
        'dentist_id',
        'dentist_preference',
        'room',
        'appointment_date',
        'appointment_time',
        'start_at',
        'end_at',
        'checked_in_at',
        'check_in_time',
        'status',
        'booking_source',
        'visit_token',
        'visit_code',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'check_in_time' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Appointment $appointment) {
            if (!$appointment->visit_token) {
                $appointment->visit_token = Str::uuid()->toString();
            }

            if (!$appointment->visit_code) {
                $appointment->visit_code = self::generateVisitCode($appointment->appointment_date ?? now());
            }
        });
    }

    public static function generateVisitCode($date): string
    {
        $dateObj = Carbon::parse($date);
        $base = $dateObj->format('Ymd');
        $count = self::whereDate('appointment_date', $dateObj->toDateString())->count() + 1;
        return sprintf('DNT-%s-%03d', $base, $count);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function dentist()
    {
        return $this->belongsTo(Dentist::class);
    }

    public function queue()
    {
        return $this->hasOne(Queue::class);
    }

    public function feedback()
    {
        return $this->hasOne(Feedback::class);
    }

    /**
     * Check if appointment has been checked in
     */
    public function hasCheckedIn(): bool
    {
        return in_array($this->status, ['arrived', 'in_queue', 'in_treatment', 'completed']);
    }

    /**
     * Check if appointment is in queue or being treated
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['arrived', 'in_queue', 'in_treatment']);
    }

    /**
     * Mark as arrived
     */
    public function markArrived(): void
    {
        $this->update([
            'status' => 'arrived',
            'check_in_time' => now(),
        ]);
    }

    /**
     * Mark as in treatment
     */
    public function markInTreatment(): void
    {
        $this->update(['status' => 'in_treatment']);
    }

    /**
     * Mark as completed
     */
    public function markCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    /**
     * Mark as late
     */
    public function markLate(): void
    {
        $this->update(['status' => 'late']);
    }

    /**
     * Mark as no-show
     */
    public function markNoShow(): void
    {
        $this->update(['status' => 'no_show']);
    }
}

