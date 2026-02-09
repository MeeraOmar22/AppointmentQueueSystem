<?php

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * AppointmentStateChanged Event
 * 
 * Fired whenever an appointment changes state (status).
 * 
 * Listeners automatically handle:
 * - Queue creation for CHECKED_IN
 * - Queue deletion for CANCELLED/NO_SHOW
 * - Room assignment for IN_TREATMENT
 * - WhatsApp notifications for state changes
 * - Feedback scheduling for COMPLETED
 * 
 * Broadcast to staff dashboard for live updates.
 */
class AppointmentStateChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Appointment $appointment,
        public string $previousStatus,
        public string $newStatus,
        public string $reason = 'Unknown',
        public array $metadata = [],
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            // Broadcast to all staff dashboard users
            new Channel('staff.dashboard.' . $this->appointment->clinic_location),
            
            // Broadcast to specific appointment (for patient portal)
            new PrivateChannel('appointment.' . $this->appointment->id),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'appointment_id' => $this->appointment->id,
            'patient_name' => $this->appointment->patient_name,
            'status' => $this->newStatus,
            'previous_status' => $this->previousStatus,
            'reason' => $this->reason,
            'queue_number' => $this->appointment->queue?->queue_number,
            'room' => $this->appointment->queue?->treatment_room_id 
                ? optional(app('App\Models\Room')::find($this->appointment->queue->treatment_room_id))->room_number 
                : null,
            'timestamp' => now()->toIso8601String(),
            'metadata' => $this->metadata,
        ];
    }

    /**
     * Get the name of the event for broadcasting.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'appointment.status_changed';
    }
}
