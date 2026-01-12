<?php

namespace Tests\Feature\Integration;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QueueIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $service;
    protected $dentist;

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
    }

    /**
     * Test pause and resume queue flow
     */
    public function test_pause_and_resume_queue_flow()
    {
        $appointment = Appointment::create([
            'patient_name' => 'Test Patient',
            'patient_phone' => '60123456789',
            'patient_email' => 'test@test.com',
            'clinic_location' => 'seremban',
            'service_id' => $this->service->id,
            'dentist_id' => $this->dentist->id,
            'appointment_date' => Carbon::today()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'checked_in',
        ]);

        // Pause queue
        $this->actingAs($this->user)->post('/staff/pause-queue');

        $settings = \DB::table('queue_settings')->first();
        $this->assertTrue((bool)$settings->is_paused);

        // Resume queue
        $this->actingAs($this->user)->post('/staff/resume-queue');

        $settings = \DB::table('queue_settings')->first();
        $this->assertFalse((bool)$settings->is_paused);
    }
}
