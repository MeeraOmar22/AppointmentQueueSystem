<?php

namespace App\Enums;

/**
 * CRIT-008 FIX: Status Enum for Consistency
 * 
 * This enum ensures all status values are:
 * - Consistent (lowercase, snake_case)
 * - Type-safe (compiler validation)
 * - Self-documenting (available statuses listed)
 * 
 * Instead of: 'IN_TREATMENT', 'in_treatment', 'inTreatment'
 * Use: AppointmentStatus::IN_TREATMENT->value
 */
enum AppointmentStatus: string
{
    case BOOKED = 'booked';
    case CONFIRMED = 'confirmed';
    case CHECKED_IN = 'checked_in';
    case WAITING = 'waiting';
    case CALLED = 'called';
    case IN_TREATMENT = 'in_treatment';
    case COMPLETED = 'completed';
    case FEEDBACK_SCHEDULED = 'feedback_scheduled';
    case FEEDBACK_SENT = 'feedback_sent';
    case NO_SHOW = 'no_show';
    case CANCELLED = 'cancelled';

    /**
     * Get human-readable label for UI
     */
    public function label(): string
    {
        return match($this) {
            self::BOOKED => 'Booked',
            self::CONFIRMED => 'Confirmed',
            self::CHECKED_IN => 'Checked In',
            self::WAITING => 'Waiting',
            self::CALLED => 'Called',
            self::IN_TREATMENT => 'In Treatment',
            self::COMPLETED => 'Completed',
            self::FEEDBACK_SCHEDULED => 'Feedback Scheduled',
            self::FEEDBACK_SENT => 'Feedback Sent',
            self::NO_SHOW => 'No Show',
            self::CANCELLED => 'Cancelled',
        };
    }

    /**
     * Check if appointment is still active (not finished)
     */
    public function isActive(): bool
    {
        return !in_array($this, [
            self::COMPLETED,
            self::FEEDBACK_SCHEDULED,
            self::FEEDBACK_SENT,
            self::NO_SHOW,
            self::CANCELLED,
        ]);
    }

    /**
     * Check if patient should be waiting or in treatment
     */
    public function isInClinic(): bool
    {
        return in_array($this, [
            self::CHECKED_IN,
            self::WAITING,
            self::IN_TREATMENT,
        ]);
    }
}
