<?php

namespace Tests\Feature\Staff;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\User;
use App\Services\StaffDashboardApiService;
use App\Services\AppointmentStateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentControllerPhase4Test extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $dentist;
    protected $appointment;

    protected function setUp(): void
    {
        parent::setUp();

        // Create authenticated user with staff role
        $this->user = User::factory()->create([
            'role' => 'staff',
            'clinic_location' => 'Main Clinic'
        ]);

        // Create dentist for assignments
        $this->dentist = Dentist::factory()->create();

        // Create test appointment
        $this->appointment = Appointment::factory()->create([
            'status' => 'CONFIRMED',
            'dentist_id' => $this->dentist->id,
            'patient_phone' => '+1234567890'
        ]);

        $this->actingAs($this->user);
    }

    /**
     * Test 1: Dashboard Index Returns Correct View
     */
    public function test_index_returns_appointments_dashboard()
    {
        $response = $this->get('/staff/appointments');

        $response->assertStatus(200)
                 ->assertViewIs('staff.appointments-realtime');
    }

    /**
     * Test 2: Get Appointments Data API Endpoint
     */
    public function test_get_appointments_data_returns_json()
    {
        $response = $this->getJson('/api/staff/appointments/today');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => [
                         'id',
                         'patient_name',
                         'phone',
                         'time',
                         'service',
                         'dentist_name',
                         'status',
                         'queue_number',
                         'room'
                     ]
                 ]);

        $this->assertCount(1, $response->json());
    }

    /**
     * Test 3: Get Summary Statistics
     */
    public function test_get_summary_statistics_returns_correct_counts()
    {
        // Create additional appointments with different statuses
        Appointment::factory()->create(['status' => 'CHECKED_IN', 'dentist_id' => $this->dentist->id]);
        Appointment::factory()->create(['status' => 'IN_TREATMENT', 'dentist_id' => $this->dentist->id]);
        Appointment::factory()->create(['status' => 'COMPLETED', 'dentist_id' => $this->dentist->id]);

        $response = $this->getJson('/api/staff/summary');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'total_appointments',
                     'checked_in',
                     'in_treatment',
                     'completed'
                 ]);

        $response->assertJson([
            'total_appointments' => 4,
            'checked_in' => 1,
            'in_treatment' => 1,
            'completed' => 1
        ]);
    }

    /**
     * Test 4: Get Single Appointment Details
     */
    public function test_show_returns_single_appointment()
    {
        $response = $this->getJson("/api/staff/appointments/{$this->appointment->id}");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'id',
                     'patient_name',
                     'phone',
                     'email',
                     'time',
                     'service',
                     'dentist_id',
                     'dentist_name',
                     'status',
                     'notes',
                     'queue_number',
                     'room'
                 ]);

        $response->assertJson([
            'id' => $this->appointment->id,
            'patient_name' => $this->appointment->patient_name
        ]);
    }

    /**
     * Test 5: Get Active Queue
     */
    public function test_get_active_queue_returns_queue_entries()
    {
        // Create checked-in appointment (should appear in queue)
        Appointment::factory()->create([
            'status' => 'CHECKED_IN',
            'dentist_id' => $this->dentist->id,
            'created_at' => now()->subMinutes(30)
        ]);

        $response = $this->getJson('/api/staff/queue');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     '*' => [
                         'appointment_id',
                         'patient_name',
                         'queue_number',
                         'wait_time',
                         'status'
                     ]
                 ]);
    }

    /**
     * Test 6: Check In - Valid Transition
     */
    public function test_check_in_transitions_status_and_fires_event()
    {
        $this->expectsEvents(\App\Events\AppointmentStateChanged::class);

        $response = $this->postJson(
            "/api/staff/appointments/{$this->appointment->id}/check-in"
        );

        $response->assertStatus(200);

        $this->appointment->refresh();
        $this->assertEquals('CHECKED_IN', $this->appointment->status);
    }

    /**
     * Test 7: Check In - Invalid Transition
     */
    public function test_check_in_fails_when_already_checked_in()
    {
        $this->appointment->update(['status' => 'CHECKED_IN']);

        $response = $this->postJson(
            "/api/staff/appointments/{$this->appointment->id}/check-in"
        );

        $response->assertStatus(409);
        $response->assertJson(['error' => true]);
    }

    /**
     * Test 8: Call Next Patient
     */
    public function test_call_next_patient_transitions_to_in_treatment()
    {
        $this->appointment->update(['status' => 'CHECKED_IN']);

        $response = $this->postJson(
            "/api/staff/appointments/{$this->appointment->id}/call-next"
        );

        $response->assertStatus(200);

        $this->appointment->refresh();
        $this->assertEquals('IN_TREATMENT', $this->appointment->status);
    }

    /**
     * Test 9: Complete Treatment
     */
    public function test_complete_treatment_transitions_to_completed()
    {
        $this->appointment->update(['status' => 'IN_TREATMENT']);

        $response = $this->postJson(
            "/api/staff/appointments/{$this->appointment->id}/complete"
        );

        $response->assertStatus(200);

        $this->appointment->refresh();
        $this->assertEquals('COMPLETED', $this->appointment->status);
    }

    /**
     * Test 10: Cancel Appointment
     */
    public function test_cancel_appointment_transitions_and_deletes_queue()
    {
        $this->appointment->update(['status' => 'CHECKED_IN']);

        // Create queue entry
        \App\Models\Queue::create([
            'appointment_id' => $this->appointment->id,
            'queue_number' => 1
        ]);

        $response = $this->postJson(
            "/api/staff/appointments/{$this->appointment->id}/cancel",
            ['reason' => 'Patient requested cancellation']
        );

        $response->assertStatus(200);

        $this->appointment->refresh();
        $this->assertEquals('CANCELLED', $this->appointment->status);

        // Verify queue entry deleted
        $this->assertNull(
            \App\Models\Queue::where('appointment_id', $this->appointment->id)->first()
        );
    }

    /**
     * Test 11: Assign Room
     */
    public function test_assign_room_updates_appointment()
    {
        $response = $this->patchJson(
            "/api/staff/appointments/{$this->appointment->id}/assign-room",
            ['room' => 'Room 1']
        );

        $response->assertStatus(200);

        $this->appointment->refresh();
        $this->assertEquals('Room 1', $this->appointment->room);
    }

    /**
     * Test 12: Create Walk-In Appointment
     */
    public function test_store_walk_in_creates_new_appointment()
    {
        $response = $this->postJson('/api/staff/appointments/walk-in', [
            'patient_name' => 'John Walk-In',
            'phone' => '+1111111111',
            'service' => 'Cleaning',
            'dentist_id' => $this->dentist->id
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['id', 'patient_name', 'status']);

        $this->assertDatabaseHas('appointments', [
            'patient_name' => 'John Walk-In',
            'status' => 'CONFIRMED'
        ]);
    }

    /**
     * Test 13: Authorization - Unauthorized User Cannot Access
     */
    public function test_unauthorized_user_cannot_access_appointments()
    {
        $otherUser = User::factory()->create(['role' => 'patient']);
        $this->actingAs($otherUser);

        $response = $this->getJson('/api/staff/appointments/today');

        $response->assertStatus(403);
    }

    /**
     * Test 14: Appointment Not Found
     */
    public function test_show_returns_404_for_nonexistent_appointment()
    {
        $response = $this->getJson('/api/staff/appointments/99999');

        $response->assertStatus(404);
    }

    /**
     * Test 15: Multiple Status Transitions
     */
    public function test_full_appointment_lifecycle()
    {
        // CONFIRMED → CHECKED_IN
        $this->postJson("/api/staff/appointments/{$this->appointment->id}/check-in");
        $this->appointment->refresh();
        $this->assertEquals('CHECKED_IN', $this->appointment->status);

        // CHECKED_IN → IN_TREATMENT
        $this->postJson("/api/staff/appointments/{$this->appointment->id}/call-next");
        $this->appointment->refresh();
        $this->assertEquals('IN_TREATMENT', $this->appointment->status);

        // IN_TREATMENT → COMPLETED
        $this->postJson("/api/staff/appointments/{$this->appointment->id}/complete");
        $this->appointment->refresh();
        $this->assertEquals('COMPLETED', $this->appointment->status);
    }
}
