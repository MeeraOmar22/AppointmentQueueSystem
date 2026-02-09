<?php

namespace App\Listeners;

use App\Events\AppointmentStateChanged;
use App\Models\Queue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

/**
 * Queue Management Listener
 * 
 * Handles automatic queue creation and deletion based on appointment status.
 * 
 * - CHECKED_IN: Creates queue if not exists
 * - WAITING/IN_TREATMENT: Queue exists and is maintained
 * - COMPLETED: Queue marked as completed
 * - CANCELLED/NO_SHOW: Queue deleted/archived
 */
class ManageQueueOnStateChange
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     * 
     * CRITICAL: This handler is SYNCHRONOUS (not queued)
     * Queue creation MUST complete before appointment status is confirmed
     * Otherwise: WhatsApp notifications are sent before queue exists
     */
    public function handle(AppointmentStateChanged $event): void
    {
        $appointment = $event->appointment;
        $newStatus = $event->newStatus;

        match ($newStatus) {
            'CHECKED_IN' => $this->createQueue($appointment),
            'COMPLETED' => $this->completeQueue($appointment),
            'CANCELLED', 'NO_SHOW' => $this->deleteQueue($appointment),
            default => null,
        };
    }

    /**
     * Create queue entry when appointment is checked in
     * 
     * CRITICAL: Queue number MUST be per-clinic
     * Seremban and Kuala Pilah should have separate sequences
     */
    private function createQueue($appointment): void
    {
        // Only create if queue doesn't exist
        if ($appointment->queue) {
            return;
        }

        // FIX #1: Get next queue number PER CLINIC LOCATION
        // NOT across all clinics (was causing duplicate numbers)
        $lastQueue = Queue::whereDate('created_at', now())
            ->whereHas('appointment', function($q) use ($appointment) {
                $q->where('clinic_location', $appointment->clinic_location);
            })
            ->orderByDesc('id')
            ->first();
        $nextNumber = ($lastQueue?->queue_number ?? 0) + 1;

        Queue::create([
            'appointment_id' => $appointment->id,
            'clinic_location' => $appointment->clinic_location,
            'queue_number' => $nextNumber,
            'queue_status' => 'waiting',
            'check_in_time' => now(),
        ]);

        logger()->info('Queue created for appointment', [
            'appointment_id' => $appointment->id,
            'queue_number' => $nextNumber,
        ]);
    }

    /**
     * Mark queue as completed when appointment is completed
     */
    private function completeQueue($appointment): void
    {
        if (!$appointment->queue) {
            return;
        }

        $appointment->queue->update([
            'queue_status' => 'completed',
            'completed_time' => now(),
        ]);

        logger()->info('Queue marked as completed', [
            'appointment_id' => $appointment->id,
            'queue_id' => $appointment->queue->id,
        ]);
    }

    /**
     * Delete queue when appointment is cancelled or no-show
     */
    private function deleteQueue($appointment): void
    {
        if (!$appointment->queue) {
            return;
        }

        $queueId = $appointment->queue->id;
        $appointment->queue->delete();

        logger()->info('Queue deleted for appointment', [
            'appointment_id' => $appointment->id,
            'queue_id' => $queueId,
        ]);
    }
}
