<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TreatmentCompletionFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $service;
    protected $dentist;
    protected $appointment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Dr. Test',
            'email' => 'dr@test.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
        ]);

        $this->service = Service::create([
            'name' => 'Cleaning',
            'description' => 'Teeth cleaning',
            'estimated_duration' => 30,
            'duration_minutes' => 30,
            'status' => 1,
        ]);

        $this->dentist = Dentist::create([
            'name' => 'Dr. Test',
            'email' => 'dr@test.com',
            'phone' => '60123456789',
            'status' => true,
        ]);

        $this->appointment = Appointment::create([
            'patient_name' => 'John Doe',
            'patient_phone' => '60123456789',
            'patient_email' => 'john@test.com',
            'clinic_location' => 'seremban',
            'service_id' => $this->service->id,
            'dentist_id' => $this->dentist->id,
            'appointment_date' => Carbon::today()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'in_treatment',
        ]);

        Queue::create([
            'appointment_id' => $this->appointment->id,
            'queue_number' => 1,
            'queue_status' => 'in_treatment',
            'check_in_time' => now(),
        ]);
    }

    /**
     * Test staff can view treatment completion page
     */
    public function test_staff_can_view_treatment_completion_page()
    {
        $this->actingAs($this->user)
            ->get('/staff/treatment-completion')
            ->assertStatus(200)
            ->assertViewIs('staff.treatment-completion');
    }

    /**
     * Test page shows current patient in treatment
     */
    public function test_treatment_completion_page_shows_current_patient()
    {
        $this->actingAs($this->user)
            ->get('/staff/treatment-completion')
            ->assertStatus(200)
            ->assertViewHas('currentPatient')
            ->assertSee($this->appointment->patient_name);
    }

    /**
     * Test page shows queue status
     */
    public function test_treatment_completion_page_shows_queue_status()
    {
        $this->actingAs($this->user)
            ->get('/staff/treatment-completion')
            ->assertStatus(200)
            ->assertViewHas('isPaused')
            ->assertViewHas('treatmentRooms');
    }

    /**
     * Test mark appointment as completed
     */
    public function test_staff_can_mark_appointment_completed()
    {
        $this->actingAs($this->user)
            ->post("/staff/treatment-completion/{$this->appointment->id}", [
                'treatment_room_id' => null,
            ])
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test completion updates queue status
     */
    public function test_treatment_completion_updates_queue_status()
    {
        $this->actingAs($this->user)
            ->post("/staff/treatment-completion/{$this->appointment->id}", [
                'treatment_room_id' => null,
            ]);

        $queue = Queue::where('appointment_id', $this->appointment->id)->first();
        
        $this->assertEquals('completed', $queue->queue_status);
    }

    /**
     * Test pause queue functionality
     */
    public function test_staff_can_pause_queue()
    {
        $this->actingAs($this->user)
            ->post('/staff/pause-queue')
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test resume queue functionality
     */
    public function test_staff_can_resume_queue()
    {
        // First pause
        $this->actingAs($this->user)->post('/staff/pause-queue');

        // Then resume
        $this->actingAs($this->user)
            ->post('/staff/resume-queue')
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /**
     * Test page shows next patient
     */
    public function test_treatment_completion_page_shows_next_patient()
    {
        $nextAppointment = Appointment::create([
            'patient_name' => 'Jane Doe',
            'patient_phone' => '60187654321',
            'patient_email' => 'jane@test.com',
            'clinic_location' => 'seremban',
            'service_id' => $this->service->id,
            'dentist_id' => $this->dentist->id,
            'appointment_date' => Carbon::today()->toDateString(),
            'appointment_time' => '11:00',
            'status' => 'checked_in',
        ]);

        Queue::create([
            'appointment_id' => $nextAppointment->id,
            'queue_number' => 2,
            'queue_status' => 'called',
            'check_in_time' => now(),
        ]);

        $this->actingAs($this->user)
            ->get('/staff/treatment-completion')
            ->assertStatus(200)
            ->assertViewHas('nextPatient');
    }

    /**
     * Test waiting count is displayed
     */
    public function test_treatment_completion_shows_waiting_count()
    {
        $this->actingAs($this->user)
            ->get('/staff/treatment-completion')
            ->assertStatus(200)
            ->assertViewHas('waitingCount');
    }
}
