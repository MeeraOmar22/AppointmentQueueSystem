<?php

namespace Tests\Feature\Api;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Queue;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueApiTest extends TestCase
{
    use RefreshDatabase;

    protected $service;
    protected $dentist;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Test Staff',
            'email' => 'staff@test.com',
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
    }

    /**
     * Test queue status API returns correct structure
     */
    public function test_queue_status_api_returns_correct_structure()
    {
        $this->actingAs($this->user)
            ->get('/api/queue/status')
            ->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'current_patient',
                'next_patients',
                'waiting_count',
                'total_count',
            ]);
    }

    /**
     * Test queue status API with no patients
     */
    public function test_queue_status_api_empty_queue()
    {
        $response = $this->actingAs($this->user)
            ->get('/api/queue/status');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'queue_empty',
                'waiting_count' => 0,
            ]);
    }

    /**
     * Test queue status API with current patient
     */
    public function test_queue_status_api_with_current_patient()
    {
        $appointment = Appointment::create([
            'patient_name' => 'Current Patient',
            'patient_phone' => '60123456789',
            'patient_email' => 'current@test.com',
            'clinic_location' => 'seremban',
            'service_id' => $this->service->id,
            'dentist_id' => $this->dentist->id,
            'appointment_date' => Carbon::today()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'in_treatment',
        ]);

        Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'in_treatment',
            'check_in_time' => now(),
        ]);

        $this->actingAs($this->user)
            ->get('/api/queue/status')
            ->assertStatus(200)
            ->assertJsonPath('current_patient.patient_name', 'Current Patient');
    }

    /**
     * Test queue status API includes waiting patients count
     */
    public function test_queue_status_api_includes_waiting_count()
    {
        for ($i = 1; $i <= 3; $i++) {
            $appointment = Appointment::create([
                'patient_name' => "Waiting Patient $i",
                'patient_phone' => '601234567' . $i,
                'patient_email' => "waiting$i@test.com",
                'clinic_location' => 'seremban',
                'service_id' => $this->service->id,
                'dentist_id' => $this->dentist->id,
                'appointment_date' => Carbon::today()->toDateString(),
                'appointment_time' => '10:' . ($i * 10),
                'status' => 'checked_in',
            ]);

            Queue::create([
                'appointment_id' => $appointment->id,
                'queue_number' => $i,
                'queue_status' => 'waiting',
                'check_in_time' => now(),
            ]);
        }

        $this->actingAs($this->user)
            ->get('/api/queue/status')
            ->assertStatus(200)
            ->assertJsonPath('waiting_count', 3);
    }

    /**
     * Test queue status API includes next patients
     */
    public function test_queue_status_api_includes_next_patients()
    {
        // Create next patients
        for ($i = 1; $i <= 2; $i++) {
            $appointment = Appointment::create([
                'patient_name' => "Next Patient $i",
                'patient_phone' => '601111111' . $i,
                'patient_email' => "next$i@test.com",
                'clinic_location' => 'seremban',
                'service_id' => $this->service->id,
                'dentist_id' => $this->dentist->id,
                'appointment_date' => Carbon::today()->toDateString(),
                'appointment_time' => '11:' . ($i * 10),
                'status' => 'checked_in',
            ]);

            Queue::create([
                'appointment_id' => $appointment->id,
                'queue_number' => $i,
                'queue_status' => 'called',
                'check_in_time' => now(),
            ]);
        }

        $this->actingAs($this->user)
            ->get('/api/queue/status')
            ->assertStatus(200)
            ->assertJsonStructure([
                'next_patients' => [
                    '*' => [
                        'queue_number',
                        'patient_name',
                        'service_name',
                    ]
                ]
            ]);
    }
}
