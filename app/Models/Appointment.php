<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Services\AppointmentStateService;
use App\Enums\AppointmentStatus;

class Appointment extends Model
{
    use SoftDeletes;
    // State machine constants
    const STATE_BOOKED = 'booked';
    const STATE_CONFIRMED = 'confirmed';
    const STATE_CANCELLED = 'cancelled';
    const STATE_NO_SHOW = 'no_show';
    const STATE_CHECKED_IN = 'checked_in';
    const STATE_WAITING = 'waiting';
    const STATE_IN_TREATMENT = 'in_treatment';
    const STATE_COMPLETED = 'completed';
    const STATE_FEEDBACK_SCHEDULED = 'feedback_scheduled';
    const STATE_FEEDBACK_SENT = 'feedback_sent';

    protected $fillable = [
        'user_id',
        'clinic_id',
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
        'actual_start_time',
        'actual_end_time',
        'status',
        'checked_in_at',
        'treatment_started_at',
        'treatment_ended_at',
        'feedback_sent_at',
        'check_in_time',
        'booking_source',
        'visit_token',
        'visit_code',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'actual_start_time' => 'datetime',
        'actual_end_time' => 'datetime',
        'checked_in_at' => 'datetime',
        'check_in_time' => 'datetime',
        'treatment_started_at' => 'datetime',
        'treatment_ended_at' => 'datetime',
        'feedback_sent_at' => 'datetime',
        'status' => AppointmentStatus::class,
    ];

    protected static function booted(): void
    {
        static::creating(function (Appointment $appointment) {
            // Set default status to BOOKED
            if (!$appointment->status) {
                $appointment->status = self::STATE_BOOKED;
            }

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

    /**
     * CRIT-001/002 FIX: Relationship to authenticated user
     * Ensures appointments are always tied to a specific user (not just email/phone)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
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

    // ============================================
    // STATE QUERY SCOPES (for state machine)
    // ============================================

    /**
     * Get appointments in waiting state
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', self::STATE_WAITING);
    }

    /**
     * Get appointments in treatment
     */
    public function scopeInTreatment($query)
    {
        return $query->where('status', self::STATE_IN_TREATMENT);
    }

    /**
     * Get appointments checked in
     */
    public function scopeCheckedIn($query)
    {
        return $query->where('status', self::STATE_CHECKED_IN);
    }

    /**
     * Get appointments completed
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATE_COMPLETED);
    }

    /**
     * Get appointments in terminal state
     */
    public function scopeTerminal($query)
    {
        return $query->whereIn('status', [
            self::STATE_CANCELLED,
            self::STATE_NO_SHOW,
            self::STATE_FEEDBACK_SENT,
        ]);
    }

    /**
     * Get active appointments (in progress)
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            self::STATE_CHECKED_IN,
            self::STATE_WAITING,
            self::STATE_IN_TREATMENT,
        ]);
    }

    /**
     * Get today's appointments by status
     */
    public function scopeToday($query)
    {
        return $query->whereDate('appointment_date', Carbon::today());
    }

    // ============================================
    // STATE MACHINE HELPER METHODS
    // ============================================

    /**
     * Check if appointment has been checked in
     */
    public function hasCheckedIn(): bool
    {
        return in_array($this->status, [
            self::STATE_CHECKED_IN,
            self::STATE_WAITING,
            self::STATE_IN_TREATMENT,
            self::STATE_COMPLETED,
            self::STATE_FEEDBACK_SCHEDULED,
            self::STATE_FEEDBACK_SENT,
        ]);
    }

    /**
     * Check if appointment is active (in progress)
     */
    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATE_CHECKED_IN,
            self::STATE_WAITING,
            self::STATE_IN_TREATMENT,
        ]);
    }

    /**
     * Check if appointment is terminal (no further changes)
     */
    public function isTerminal(): bool
    {
        return in_array($this->status, [
            self::STATE_CANCELLED,
            self::STATE_NO_SHOW,
            self::STATE_FEEDBACK_SENT,
        ]);
    }

    /**
     * Check if can accept next state
     */
    public function canTransitionTo(string $newState): bool
    {
        $stateService = app(AppointmentStateService::class);
        return $stateService->isValidTransition($this->status, $newState);
    }

    /**
     * DEPRECATED: Use AppointmentStateService instead
     * These methods are kept for backward compatibility
     */
    public function markArrived(): void
    {
        $stateService = app(AppointmentStateService::class);
        $stateService->transitionTo($this, self::STATE_CHECKED_IN, 'Legacy markArrived() call');
    }

    public function markInTreatment(): void
    {
        $stateService = app(AppointmentStateService::class);
        $stateService->transitionTo($this, self::STATE_IN_TREATMENT, 'Legacy markInTreatment() call');
    }

    public function markCompleted(): void
    {
        $stateService = app(AppointmentStateService::class);
        $stateService->transitionTo($this, self::STATE_COMPLETED, 'Legacy markCompleted() call');
    }

    public function markLate(): void
    {
        // Late is not part of new state machine, treat as NO_SHOW
        $stateService = app(AppointmentStateService::class);
        $stateService->transitionTo($this, self::STATE_NO_SHOW, 'Marked as late/no-show');
    }

    public function markNoShow(): void
    {
        $stateService = app(AppointmentStateService::class);
        $stateService->transitionTo($this, self::STATE_NO_SHOW, 'Marked as no-show');
    }

    /**
     * Get expected appointment duration in minutes
     * 
     * @return int Service duration or 30 minutes default
     */
    public function getExpectedDurationMinutes(): int
    {
        return (int)($this->service?->estimated_duration ?? 30);
    }

    /**
     * Calculate expected appointment end time based on start time and service duration
     * 
     * @return Carbon
     */
    public function getExpectedEndTime(): Carbon
    {
        $startTime = $this->start_at ?? Carbon::createFromFormat('Y-m-d H:i', 
            $this->appointment_date->format('Y-m-d') . ' ' . $this->appointment_time
        );
        
        return $startTime->copy()->addMinutes($this->getExpectedDurationMinutes());
    }

    /**
     * Get actual treatment duration in minutes
     * Returns null if treatment not yet completed
     * 
     * @return int|null Actual duration in minutes
     */
    public function getActualDurationMinutes(): ?int
    {
        if (!$this->actual_start_time || !$this->actual_end_time) {
            return null;
        }

        return (int)$this->actual_start_time->diffInMinutes($this->actual_end_time);
    }

    /**
     * Check if actual treatment duration exceeded expected duration
     * 
     * @return bool True if treatment ran over expected time
     */
    public function didTreatmentRunOver(): bool
    {
        $actualDuration = $this->getActualDurationMinutes();
        if ($actualDuration === null) {
            return false;
        }

        return $actualDuration > $this->getExpectedDurationMinutes();
    }

    /**
     * Get minutes by which treatment overran expected duration
     * Returns negative number if finished early
     * 
     * @return int|null Delay in minutes (positive = late, negative = early, null = not completed)
     */
    public function getTreatmentDelayMinutes(): ?int
    {
        $actualDuration = $this->getActualDurationMinutes();
        if ($actualDuration === null) {
            return null;
        }

        return $actualDuration - $this->getExpectedDurationMinutes();
    }

    /**
     * Record actual treatment start time
     * Used when treatment actually begins (may differ from appointment_time)
     * 
     * @param Carbon|null $time Defaults to now()
     */
    public function recordActualStartTime(?Carbon $time = null): void
    {
        $this->update([
            'actual_start_time' => $time ?? now(),
        ]);
    }

    /**
     * Record actual treatment end time
     * Used to calculate real treatment duration and adjust queue ETAs
     * 
     * @param Carbon|null $time Defaults to now()
     */
    public function recordActualEndTime(?Carbon $time = null): void
    {
        $this->update([
            'actual_end_time' => $time ?? now(),
        ]);
    }
}


