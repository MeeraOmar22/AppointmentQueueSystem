<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class FeedbackRequest extends Model
{
    protected $table = 'feedback_requests';

    protected $fillable = [
        'appointment_id',
        'patient_name',
        'patient_phone',
        'patient_email',
        'request_sent_at',
        'response_received_at',
        'response_status',
        'sent_via',
        'reminder_count',
        'last_reminder_sent_at',
    ];

    protected $casts = [
        'request_sent_at' => 'datetime',
        'response_received_at' => 'datetime',
        'last_reminder_sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the appointment this feedback request is for.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get the feedback received for this request (if any).
     */
    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class, 'appointment_id', 'appointment_id');
    }

    /**
     * Check if feedback request is pending.
     */
    public function isPending(): bool
    {
        return $this->response_status === 'pending';
    }

    /**
     * Check if feedback request is overdue (7+ days without response).
     */
    public function isOverdue(): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        if (!$this->request_sent_at) {
            return false;
        }

        return $this->request_sent_at->diffInDays(now()) > 7;
    }

    /**
     * Check if feedback request is critically overdue (14+ days).
     */
    public function isCriticallyOverdue(): bool
    {
        if (!$this->isPending()) {
            return false;
        }

        if (!$this->request_sent_at) {
            return false;
        }

        return $this->request_sent_at->diffInDays(now()) > 14;
    }

    /**
     * Get days since request was sent.
     */
    public function daysSinceSent(): int
    {
        if (!$this->request_sent_at) {
            return 0;
        }

        return $this->request_sent_at->diffInDays(now());
    }

    /**
     * Get human-readable status.
     */
    public function getStatusLabel(): string
    {
        return match($this->response_status) {
            'pending' => 'Awaiting Response',
            'responded' => 'Responded',
            'expired' => 'Expired',
            'not_sent' => 'Not Sent',
            default => 'Unknown',
        };
    }

    /**
     * Mark feedback request as responded.
     */
    public function markAsResponded(): void
    {
        $this->update([
            'response_status' => 'responded',
            'response_received_at' => now(),
        ]);
    }

    /**
     * Mark feedback request as expired.
     */
    public function markAsExpired(): void
    {
        $this->update([
            'response_status' => 'expired',
        ]);
    }

    /**
     * Increment reminder count and update last reminder timestamp.
     */
    public function recordReminder(): void
    {
        $this->update([
            'reminder_count' => $this->reminder_count + 1,
            'last_reminder_sent_at' => now(),
        ]);
    }

    /**
     * Scope: Get all pending requests.
     */
    public function scopePending($query)
    {
        return $query->where('response_status', 'pending');
    }

    /**
     * Scope: Get all overdue requests.
     */
    public function scopeOverdue($query)
    {
        return $query->where('response_status', 'pending')
            ->where('request_sent_at', '<', now()->subDays(7));
    }

    /**
     * Scope: Get all critically overdue requests.
     */
    public function scopeCriticallyOverdue($query)
    {
        return $query->where('response_status', 'pending')
            ->where('request_sent_at', '<', now()->subDays(14));
    }

    /**
     * Scope: Get recent requests (within last 30 days).
     */
    public function scopeRecent($query)
    {
        return $query->where('request_sent_at', '>=', now()->subDays(30));
    }
}
