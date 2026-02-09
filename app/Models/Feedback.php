<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\AppointmentStatus;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'user_id',
        'appointment_id',
        'patient_name',
        'patient_phone',
        'rating',
        'comments',
        'service_quality',
        'staff_friendliness',
        'cleanliness',
        'would_recommend',
        'feedback_request_sent_at',
        'feedback_response_received_at',
        'is_responded',
        'feedback_request_method',
    ];

    protected $casts = [
        'would_recommend' => 'boolean',
        'is_responded' => 'boolean',
        'feedback_request_sent_at' => 'datetime',
        'feedback_response_received_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the appointment that this feedback is for.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get the feedback request this feedback is responding to.
     */
    public function feedbackRequest(): BelongsTo
    {
        return $this->belongsTo(FeedbackRequest::class, 'appointment_id', 'appointment_id');
    }

    /**
     * CRIT-001 FIX: Relationship to authenticated user
     * Ensures feedback is tied to specific user (not just phone/name)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Boot the model - handle feedback response lifecycle
     */
    protected static function booted(): void
    {
        /**
         * When feedback is created (patient responds):
         * 1. Mark feedback request as responded
         * 2. Update appointment status to FEEDBACK_SENT
         * 3. Record response timestamp
         */
        static::created(function (Feedback $feedback) {
            // Update feedback request
            FeedbackRequest::where('appointment_id', $feedback->appointment_id)
                ->update([
                    'response_received_at' => now(),
                    'response_status' => 'responded',
                ]);

            // Update appointment status
            $appointment = $feedback->appointment;
            if ($appointment && $appointment->status != AppointmentStatus::FEEDBACK_SENT) {
                $appointment->update([
                    'status' => AppointmentStatus::FEEDBACK_SENT,
                    'feedback_sent_at' => now(),
                ]);
            }

            // Set flags on feedback record
            $feedback->update([
                'feedback_response_received_at' => now(),
                'is_responded' => true,
            ]);
        });
    }
}
