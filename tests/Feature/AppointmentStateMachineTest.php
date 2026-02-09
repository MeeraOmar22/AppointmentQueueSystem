<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Dentist;
use App\Models\Queue;
use App\Services\AppointmentStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AppointmentStateMachineTest extends TestCase
{
    use RefreshDatabase;

    protected AppointmentStateService $stateService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->stateService = app(AppointmentStateService::class);
    }

    private function createAppointment($status = 'booked', $attributes = []): Appointment
    {
        return Appointment::create(array_merge([
            'patient_name' => 'John Doe',
            'patient_phone' => '+60123456789',
            'patient_email' => 'john@example.com',
            'clinic_location' => 'seremban',
            'service_id' => Service::create([
                'name' => 'Checkup',
                'estimated_duration' => 30,
                'duration_minutes' => 30,
                'price' => 100,
                'status' => 1
            ])->id,
            'dentist_id' => Dentist::create(['name' => 'Dr. Smith', 'status' => 1])->id,
            'appointment_date' => now()->addDay(),
            'appointment_time' => '10:00',
            'status' => $status,
        ], $attributes));
    }

    public function test_new_appointment_starts_in_booked_state()
    {
        $appointment = Appointment::create([
            'patient_name' => 'John Doe',
            'patient_phone' => '+60123456789',
            'patient_email' => 'john@example.com',
            'clinic_location' => 'seremban',
            'service_id' => Service::create([
                'name' => 'Checkup',
                'estimated_duration' => 30,
                'duration_minutes' => 30,
                'price' => 100,
                'status' => 1
            ])->id,
            'dentist_id' => Dentist::create(['name' => 'Dr. Smith', 'status' => 1])->id,
            'appointment_date' => now()->addDay(),
            'appointment_time' => '10:00',
        ]);

        $this->assertEquals('booked', $appointment->status);
    }

    public function test_booked_can_transition_to_confirmed()
    {
        $appointment = $this->createAppointment('booked');

        $result = $this->stateService->transitionTo($appointment, 'confirmed', 'Test transition');

        $this->assertTrue($result);
        $this->assertEquals('confirmed', $appointment->fresh()->status);
    }

    public function test_booked_can_transition_to_cancelled()
    {
        $appointment = $this->createAppointment('booked');

        $result = $this->stateService->transitionTo($appointment, 'cancelled', 'User cancelled');

        $this->assertTrue($result);
        $this->assertEquals('cancelled', $appointment->fresh()->status);
    }

    public function test_confirmed_can_transition_to_checked_in()
    {
        $appointment = $this->createAppointment('confirmed');

        $result = $this->stateService->transitionTo($appointment, 'checked_in', 'Patient checked in');

        $this->assertTrue($result);
        $this->assertEquals('checked_in', $appointment->fresh()->status);
        // Should create queue
        $this->assertNotNull($appointment->fresh()->queue);
    }

    public function test_confirmed_can_transition_to_no_show()
    {
        $appointment = $this->createAppointment('confirmed');

        $result = $this->stateService->transitionTo($appointment, 'no_show', 'No show auto-marked');

        $this->assertTrue($result);
        $this->assertEquals('no_show', $appointment->fresh()->status);
    }

    public function test_checked_in_can_transition_to_waiting()
    {
        $appointment = $this->createAppointment('checked_in');
        Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
            'clinic_location' => $appointment->clinic_location,
        ]);

        $result = $this->stateService->transitionTo($appointment, 'waiting', 'Patient queued');

        $this->assertTrue($result);
        $this->assertEquals('waiting', $appointment->fresh()->status);
    }

    public function test_waiting_can_transition_to_in_treatment()
    {
        $appointment = $this->createAppointment('waiting', ['room' => '101']);
        Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
            'clinic_location' => $appointment->clinic_location,
        ]);

        $result = $this->stateService->transitionTo($appointment, 'in_treatment', 'Assigned to treatment');

        $this->assertTrue($result);
        $this->assertEquals('in_treatment', $appointment->fresh()->status);
        $this->assertNotNull($appointment->fresh()->treatment_started_at);
    }

    public function test_in_treatment_can_transition_to_completed()
    {
        $appointment = $this->createAppointment('in_treatment', [
            'treatment_started_at' => now()->subHour(),
        ]);
        Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'in_treatment',
            'clinic_location' => $appointment->clinic_location,
        ]);

        $result = $this->stateService->transitionTo($appointment, 'completed', 'Treatment complete');

        $this->assertTrue($result);
        // Should auto-transition to feedback_scheduled
        $this->assertEquals('feedback_scheduled', $appointment->fresh()->status);
        $this->assertNotNull($appointment->fresh()->treatment_ended_at);
    }

    public function test_invalid_transition_fails()
    {
        $appointment = $this->createAppointment('booked');

        // Booked cannot go directly to in_treatment
        $result = $this->stateService->transitionTo($appointment, 'in_treatment');

        $this->assertFalse($result);
        $this->assertEquals('booked', $appointment->fresh()->status);
    }

    public function test_terminal_state_cannot_transition()
    {
        $appointment = $this->createAppointment('cancelled');

        $result = $this->stateService->transitionTo($appointment, 'confirmed');

        $this->assertFalse($result);
        $this->assertEquals('cancelled', $appointment->fresh()->status);
    }

    public function test_full_happy_path()
    {
        $appointment = $this->createAppointment('booked');

        // booked -> confirmed
        $this->assertTrue($this->stateService->transitionTo($appointment, 'confirmed'));
        $appointment->refresh();

        // confirmed -> checked_in
        $this->assertTrue($this->stateService->transitionTo($appointment, 'checked_in'));
        $appointment->refresh();
        $this->assertNotNull($appointment->queue);

        // checked_in -> waiting
        $this->assertTrue($this->stateService->transitionTo($appointment, 'waiting'));
        $appointment->refresh();

        // waiting -> in_treatment
        $appointment->update(['room' => '101']);
        $this->assertTrue($this->stateService->transitionTo($appointment, 'in_treatment'));
        $appointment->refresh();

        // in_treatment -> completed (auto -> feedback_scheduled)
        $this->assertTrue($this->stateService->transitionTo($appointment, 'completed'));
        $appointment->refresh();
        $this->assertEquals('feedback_scheduled', $appointment->status);
    }

    public function test_query_scopes()
    {
        $this->createAppointment('waiting');
        $this->createAppointment('waiting');
        $this->createAppointment('waiting');
        $this->createAppointment('in_treatment');
        $this->createAppointment('in_treatment');
        $this->createAppointment('cancelled');

        $this->assertEquals(3, Appointment::waiting()->count());
        $this->assertEquals(2, Appointment::inTreatment()->count());
        $this->assertEquals(5, Appointment::active()->count());
        $this->assertEquals(1, Appointment::terminal()->count());
    }

    public function test_state_machine_rules()
    {
        $appointment = $this->createAppointment('booked');
        
        $allowed = $this->stateService->getAllowedNextStates('booked');
        $this->assertContains('confirmed', $allowed);
        $this->assertContains('cancelled', $allowed);
        $this->assertNotContains('in_treatment', $allowed);
    }

    public function test_is_terminal_state()
    {
        $this->assertTrue($this->stateService->isTerminalState('cancelled'));
        $this->assertTrue($this->stateService->isTerminalState('no_show'));
        $this->assertTrue($this->stateService->isTerminalState('feedback_sent'));
        
        $this->assertFalse($this->stateService->isTerminalState('booked'));
        $this->assertFalse($this->stateService->isTerminalState('waiting'));
    }
}
