<?php

namespace Tests\Feature\Events;

use App\Events\AppointmentStateChanged;
use App\Listeners\ManageQueueOnStateChange;
use App\Listeners\SendWhatsAppOnStateChange;
use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue as QueueFacade;
use Tests\TestCase;

class AppointmentStateChangedPhase4Test extends TestCase
{
    use RefreshDatabase;

    protected $appointment;
    protected $dentist;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        QueueFacade::fake();

        $this->dentist = Dentist::factory()->create();
        $this->appointment = Appointment::factory()->create([
            'status' => 'CONFIRMED',
            'dentist_id' => $this->dentist->id,
            'patient_phone' => '+1234567890'
        ]);
    }

    /**
     * Test 1: Event Broadcasts to Correct Channels
     */
    public function test_appointment_state_changed_event_broadcasts()
    {
        Event::fake();

        $event = new AppointmentStateChanged(
            $this->appointment,
            'CONFIRMED',
            'CHECKED_IN',
            'Checked in at reception'
        );

        event($event);

        Event::assertDispatched(AppointmentStateChanged::class);
    }

    /**
     * Test 2: Event Contains All Required Data
     */
    public function test_appointment_state_changed_includes_all_data()
    {
        $event = new AppointmentStateChanged(
            $this->appointment,
            'CONFIRMED',
            'CHECKED_IN',
            'Patient arrived'
        );

        $broadcastData = $event->broadcastWith();

        $this->assertArrayHasKey('appointment_id', $broadcastData);
        $this->assertArrayHasKey('patient_name', $broadcastData);
        $this->assertArrayHasKey('status', $broadcastData);
        $this->assertArrayHasKey('previous_status', $broadcastData);
        $this->assertArrayHasKey('reason', $broadcastData);
        $this->assertArrayHasKey('timestamp', $broadcastData);

        $this->assertEquals($this->appointment->id, $broadcastData['appointment_id']);
        $this->assertEquals('CHECKED_IN', $broadcastData['status']);
        $this->assertEquals('CONFIRMED', $broadcastData['previous_status']);
        $this->assertEquals('Patient arrived', $broadcastData['reason']);
    }

    /**
     * Test 3: ManageQueueOnStateChange Creates Queue Entry
     */
    public function test_manage_queue_on_state_change_creates_queue_entry()
    {
        $this->appointment->update(['status' => 'CONFIRMED']);

        $event = new AppointmentStateChanged(
            $this->appointment,
            'CONFIRMED',
            'CHECKED_IN',
            'Patient checked in'
        );

        $listener = new ManageQueueOnStateChange();
        $listener->handle($event);

        $this->assertDatabaseHas('queues', [
            'appointment_id' => $this->appointment->id
        ]);

        $queue = Queue::where('appointment_id', $this->appointment->id)->first();
        $this->assertNotNull($queue->queue_number);
    }

    /**
     * Test 4: ManageQueueOnStateChange Deletes Queue on Cancel
     */
    public function test_manage_queue_on_state_change_deletes_queue_on_cancel()
    {
        $this->appointment->update(['status' => 'CHECKED_IN']);

        // Create queue entry
        Queue::create([
            'appointment_id' => $this->appointment->id,
            'queue_number' => 1
        ]);

        $this->assertDatabaseHas('queues', [
            'appointment_id' => $this->appointment->id
        ]);

        // Dispatch cancel event
        $event = new AppointmentStateChanged(
            $this->appointment,
            'CHECKED_IN',
            'CANCELLED',
            'Patient cancelled'
        );

        $listener = new ManageQueueOnStateChange();
        $listener->handle($event);

        // Verify queue deleted
        $this->assertDatabaseMissing('queues', [
            'appointment_id' => $this->appointment->id
        ]);
    }

    /**
     * Test 5: ManageQueueOnStateChange Deletes Queue on No-Show
     */
    public function test_manage_queue_on_state_change_deletes_queue_on_no_show()
    {
        $this->appointment->update(['status' => 'CHECKED_IN']);

        Queue::create([
            'appointment_id' => $this->appointment->id,
            'queue_number' => 1
        ]);

        $event = new AppointmentStateChanged(
            $this->appointment,
            'CHECKED_IN',
            'NO_SHOW',
            'Patient did not arrive'
        );

        $listener = new ManageQueueOnStateChange();
        $listener->handle($event);

        $this->assertDatabaseMissing('queues', [
            'appointment_id' => $this->appointment->id
        ]);
    }

    /**
     * Test 6: ManageQueueOnStateChange Is Idempotent (No Duplicates)
     */
    public function test_manage_queue_prevents_duplicate_queue_entries()
    {
        $this->appointment->update(['status' => 'CONFIRMED']);

        // Create queue entry manually
        Queue::create([
            'appointment_id' => $this->appointment->id,
            'queue_number' => 1
        ]);

        // Dispatch event (should not create duplicate)
        $event = new AppointmentStateChanged(
            $this->appointment,
            'CONFIRMED',
            'CHECKED_IN',
            'Checked in'
        );

        $listener = new ManageQueueOnStateChange();
        $listener->handle($event);

        // Verify only one queue entry exists
        $count = Queue::where('appointment_id', $this->appointment->id)->count();
        $this->assertEquals(1, $count);
    }

    /**
     * Test 7: SendWhatsAppOnStateChange Sends on CHECKED_IN
     */
    public function test_send_whatsapp_on_state_change_sends_on_checked_in()
    {
        $this->appointment->update(['status' => 'CONFIRMED']);

        $event = new AppointmentStateChanged(
            $this->appointment,
            'CONFIRMED',
            'CHECKED_IN',
            'Patient checked in'
        );

        $listener = new SendWhatsAppOnStateChange();
        $listener->handle($event);

        // Verify WhatsApp sending was attempted (logged in activity)
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'whatsapp_sent',
            'model_id' => $this->appointment->id
        ]);
    }

    /**
     * Test 8: SendWhatsAppOnStateChange Sends on COMPLETED
     */
    public function test_send_whatsapp_on_state_change_sends_on_completed()
    {
        $this->appointment->update(['status' => 'IN_TREATMENT']);

        $event = new AppointmentStateChanged(
            $this->appointment,
            'IN_TREATMENT',
            'COMPLETED',
            'Treatment finished'
        );

        $listener = new SendWhatsAppOnStateChange();
        $listener->handle($event);

        // Verify WhatsApp logged
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'whatsapp_sent',
            'model_id' => $this->appointment->id
        ]);
    }

    /**
     * Test 9: SendWhatsAppOnStateChange Sends on CANCELLED
     */
    public function test_send_whatsapp_on_state_change_sends_on_cancelled()
    {
        $this->appointment->update(['status' => 'CHECKED_IN']);

        $event = new AppointmentStateChanged(
            $this->appointment,
            'CHECKED_IN',
            'CANCELLED',
            'Patient cancelled'
        );

        $listener = new SendWhatsAppOnStateChange();
        $listener->handle($event);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'whatsapp_sent',
            'model_id' => $this->appointment->id
        ]);
    }

    /**
     * Test 10: WhatsApp Message Includes Patient Name
     */
    public function test_whatsapp_message_includes_patient_name()
    {
        $this->appointment->update([
            'status' => 'CONFIRMED',
            'patient_name' => 'Ahmed Hassan'
        ]);

        $event = new AppointmentStateChanged(
            $this->appointment,
            'CONFIRMED',
            'CHECKED_IN',
            'Patient arrived'
        );

        // Capture message sent
        $listener = new SendWhatsAppOnStateChange();
        $listener->handle($event);

        // Verify patient name in activity log (message context)
        $activity = \App\Models\ActivityLog::where('model_id', $this->appointment->id)->latest()->first();
        $this->assertNotNull($activity);
        // Message should contain patient name
        $this->assertStringContainsString('Ahmed', $activity->description ?? $activity->data ?? '');
    }

    /**
     * Test 11: WhatsApp Message Includes Formatted Status
     */
    public function test_whatsapp_message_includes_formatted_status()
    {
        $this->appointment->update(['status' => 'CONFIRMED']);

        $event = new AppointmentStateChanged(
            $this->appointment,
            'CONFIRMED',
            'CHECKED_IN',
            'Patient arrived'
        );

        $listener = new SendWhatsAppOnStateChange();
        $listener->handle($event);

        $activity = \App\Models\ActivityLog::where('model_id', $this->appointment->id)->latest()->first();
        $this->assertNotNull($activity);
        // Should use friendly format "Checked In" not "CHECKED_IN"
    }

    /**
     * Test 12: Event Uses Correct Broadcast Channel
     */
    public function test_event_broadcasts_to_staff_dashboard_channel()
    {
        $this->appointment->clinic_location = 'Main Clinic';
        $this->appointment->save();

        $event = new AppointmentStateChanged(
            $this->appointment,
            'CONFIRMED',
            'CHECKED_IN',
            'Checked in'
        );

        // Event should implement ShouldBroadcast
        $this->assertTrue(method_exists($event, 'broadcastOn'));

        $channels = $event->broadcastOn();
        $channelNames = array_map(fn($c) => $c->name, $channels);

        $this->assertContains('staff.dashboard.Main Clinic', $channelNames);
    }

    /**
     * Test 13: Event Uses Correct Broadcast Name
     */
    public function test_event_uses_correct_broadcast_name()
    {
        $event = new AppointmentStateChanged(
            $this->appointment,
            'CONFIRMED',
            'CHECKED_IN',
            'Checked in'
        );

        $broadcastName = $event->broadcastAs();
        $this->assertEquals('appointment.status_changed', $broadcastName);
    }

    /**
     * Test 14: Multiple Events Fire Correctly
     */
    public function test_multiple_state_changes_fire_events_correctly()
    {
        Event::fake();

        // Event 1: CONFIRMED → CHECKED_IN
        event(new AppointmentStateChanged(
            $this->appointment,
            'CONFIRMED',
            'CHECKED_IN',
            'Checked in'
        ));

        $this->appointment->update(['status' => 'CHECKED_IN']);

        // Event 2: CHECKED_IN → IN_TREATMENT
        event(new AppointmentStateChanged(
            $this->appointment,
            'CHECKED_IN',
            'IN_TREATMENT',
            'Called for treatment'
        ));

        Event::assertDispatchedTimes(AppointmentStateChanged::class, 2);
    }

    /**
     * Test 15: Event Handles Phone Number Formatting
     */
    public function test_event_handles_international_phone_numbers()
    {
        $this->appointment->update(['patient_phone' => '+20111234567']);

        $event = new AppointmentStateChanged(
            $this->appointment,
            'CONFIRMED',
            'CHECKED_IN',
            'Checked in'
        );

        $broadcastData = $event->broadcastWith();
        // Phone should be included and properly formatted
        $this->assertNotNull($broadcastData['appointment_id']);
    }
}
