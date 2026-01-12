<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Service;
use App\Models\User;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentManagementFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $service;
    protected $dentist;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test user
        $this->user = User::create([
            'name' => 'Test Staff',
            'email' => 'staff@test.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
        ]);

        // Create test service
        $this->service = Service::create([
            'name' => 'Cleaning',
            'description' => 'Teeth cleaning',
            'estimated_duration' => 30,
            'duration_minutes' => 30,
            'status' => 1,
        ]);

        // Create test dentist
        $this->dentist = Dentist::create([
            'name' => 'Dr. Test',
            'email' => 'dr@test.com',
            'phone' => '60123456789',
            'status' => true,
        ]);

        // Create dentist schedule for tomorrow at 10:00
        $tomorrow = Carbon::tomorrow();
        $this->dentist->schedules()->create([
            'day_of_week' => $tomorrow->format('l'),
            'start_time' => '09:00',
            'end_time' => '18:00',
            'date' => $tomorrow->toDateString(),
            'status' => 1,
        ]);
    }

    /**
     * Test view appointment list
     */
    public function test_staff_can_view_appointments()
    {
        $this->actingAs($this->user)
            ->get('/staff/appointments')
            ->assertStatus(200)
            ->assertViewIs('staff.appointments');
    }

    /**
     * Test create appointment
     */
    public function test_staff_can_create_appointment()
    {
        $data = [
            'patient_name' => 'John Doe',
            'patient_phone' => '60123456789',
            'patient_email' => 'john@test.com',
            'clinic_location' => 'seremban',
            'service_id' => $this->service->id,
            'dentist_id' => $this->dentist->id,
            'appointment_date' => Carbon::today()->addDay()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ];

        $this->actingAs($this->user)
            ->post('/staff/appointments', $data)
            ->assertRedirect('/staff/appointments')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', [
            'patient_name' => 'John Doe',
            'patient_email' => 'john@test.com',
        ]);
    }

    /**
     * Test update appointment
     */
    public function test_staff_can_update_appointment()
    {
        $appointment = Appointment::create([
            'patient_name' => 'Original Name',
            'patient_phone' => '60123456789',
            'patient_email' => 'john@test.com',
            'clinic_location' => 'seremban',
            'service_id' => $this->service->id,
            'dentist_id' => $this->dentist->id,
            'appointment_date' => Carbon::today()->addDay()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        $updateData = [
            'patient_name' => 'Updated Name',
            'patient_phone' => '60123456789',
            'patient_email' => 'john@test.com',
            'clinic_location' => 'seremban',
            'service_id' => $this->service->id,
            'dentist_id' => $this->dentist->id,
            'appointment_date' => Carbon::today()->addDay()->toDateString(),
            'appointment_time' => '11:00',
            'status' => 'booked',
        ];

        $this->actingAs($this->user)
            ->put("/staff/appointments/{$appointment->id}", $updateData)
            ->assertRedirect('/staff/appointments');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'patient_name' => 'Updated Name',
        ]);
    }

    /**
     * Test delete appointment
     */
    public function test_staff_can_delete_appointment()
    {
        $appointment = Appointment::create([
            'patient_name' => 'To Delete',
            'patient_phone' => '60123456789',
            'patient_email' => 'delete@test.com',
            'clinic_location' => 'seremban',
            'service_id' => $this->service->id,
            'dentist_id' => $this->dentist->id,
            'appointment_date' => Carbon::today()->addDay()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        $appointmentId = $appointment->id;

        $this->actingAs($this->user)
            ->delete("/staff/appointments/{$appointmentId}")
            ->assertRedirect();

        $this->assertDatabaseMissing('appointments', [
            'id' => $appointmentId,
        ]);
    }

    /**
     * Test validation on appointment creation
     */
    public function test_appointment_creation_requires_patient_name()
    {
        $data = [
            'patient_name' => '',
            'patient_phone' => '60123456789',
            'patient_email' => 'test@test.com',
            'clinic_location' => 'seremban',
            'service_id' => $this->service->id,
            'dentist_id' => $this->dentist->id,
            'appointment_date' => Carbon::today()->addDay()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ];

        $this->actingAs($this->user)
            ->post('/staff/appointments', $data)
            ->assertSessionHasErrors('patient_name');
    }

    /**
     * Test appointment validation - email format
     */
    public function test_appointment_email_must_be_valid()
    {
        $data = [
            'patient_name' => 'John Doe',
            'patient_phone' => '60123456789',
            'patient_email' => 'invalid-email',
            'clinic_location' => 'seremban',
            'service_id' => $this->service->id,
            'dentist_id' => $this->dentist->id,
            'appointment_date' => Carbon::today()->addDay()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ];

        $this->actingAs($this->user)
            ->post('/staff/appointments', $data)
            ->assertSessionHasErrors('patient_email');
    }

    /**
     * Test appointment with valid clinic location
     */
    public function test_appointment_clinic_location_must_be_valid()
    {
        $data = [
            'patient_name' => 'John Doe',
            'patient_phone' => '60123456789',
            'patient_email' => 'john@test.com',
            'clinic_location' => 'invalid_location',
            'service_id' => $this->service->id,
            'dentist_id' => $this->dentist->id,
            'appointment_date' => Carbon::today()->addDay()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ];

        $this->actingAs($this->user)
            ->post('/staff/appointments', $data)
            ->assertSessionHasErrors('clinic_location');
    }

    /**
     * Test appointment view page
     */
    public function test_can_view_single_appointment()
    {
        $appointment = Appointment::create([
            'patient_name' => 'John Doe',
            'patient_phone' => '60123456789',
            'patient_email' => 'john@test.com',
            'clinic_location' => 'seremban',
            'service_id' => $this->service->id,
            'dentist_id' => $this->dentist->id,
            'appointment_date' => Carbon::today()->addDay()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        $this->actingAs($this->user)
            ->get("/staff/appointments/{$appointment->id}/edit")
            ->assertStatus(200);
    }
}
