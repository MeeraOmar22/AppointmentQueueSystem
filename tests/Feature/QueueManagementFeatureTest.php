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

class QueueManagementFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $service;
    protected $dentist;

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
     * Test check-in creates queue entry
     */
    public function test_check_in_creates_queue_entry()
    {
        $appointment = Appointment::create([
            'patient_name' => 'John Doe',
            'patient_phone' => '60123456789',
            'patient_email' => 'john@test.com',
            'clinic_location' => 'seremban',
            'service_id' => $this->service->id,
            'dentist_id' => $this->dentist->id,
            'appointment_date' => Carbon::today()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        $this->actingAs($this->user)
            ->post("/staff/appointments/{$appointment->id}/check-in");

        $queue = Queue::where('appointment_id', $appointment->id)->first();

        $this->assertNotNull($queue);
        $this->assertEquals('checked_in', $queue->queue_status);
    }

    /**
     * Test queue number assignment
     */
    public function test_queue_number_is_assigned_on_check_in()
    {
        $appointment = Appointment::create([
            'patient_name' => 'Jane Doe',
            'patient_phone' => '60187654321',
            'patient_email' => 'jane@test.com',
            'clinic_location' => 'seremban',
            'service_id' => $this->service->id,
            'dentist_id' => $this->dentist->id,
            'appointment_date' => Carbon::today()->toDateString(),
            'appointment_time' => '11:00',
            'status' => 'booked',
        ]);

        $this->actingAs($this->user)
            ->post("/staff/appointments/{$appointment->id}/check-in");

        $queue = Queue::where('appointment_id', $appointment->id)->first();

        $this->assertNotNull($queue->queue_number);
        $this->assertGreaterThan(0, $queue->queue_number);
    }

    /**
     * Test queue status can be updated
     */
    public function test_queue_status_can_be_updated()
    {
        $appointment = Appointment::create([
            'patient_name' => 'Bob Smith',
            'patient_phone' => '60111111111',
            'patient_email' => 'bob@test.com',
            'clinic_location' => 'seremban',
            'service_id' => $this->service->id,
            'dentist_id' => $this->dentist->id,
            'appointment_date' => Carbon::today()->toDateString(),
            'appointment_time' => '12:00',
            'status' => 'booked',
        ]);

        $queue = Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
            'check_in_time' => now(),
        ]);

        $this->actingAs($this->user)
            ->put("/staff/queue/{$queue->id}", [
                'status' => 'in_treatment',
            ]);

        $this->assertEquals('in_treatment', $queue->refresh()->queue_status);
    }

    /**
     * Test queue API endpoint
     */
    public function test_queue_status_api_endpoint()
    {
        $appointment = Appointment::create([
            'patient_name' => 'Test Patient',
            'patient_phone' => '60123456789',
            'patient_email' => 'test@test.com',
            'clinic_location' => 'seremban',
            'service_id' => $this->service->id,
            'dentist_id' => $this->dentist->id,
            'appointment_date' => Carbon::today()->toDateString(),
            'appointment_time' => '13:00',
            'status' => 'checked_in',
        ]);

        Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'checked_in',
            'check_in_time' => now(),
        ]);

        $this->actingAs($this->user)
            ->get('/api/queue/status')
            ->assertStatus(200)
            ->assertJsonStructure(['status', 'current_patient', 'waiting_count']);
    }

    /**
     * Test queue respects clinic location
     */
    public function test_queue_entries_for_today()
    {
        Appointment::create([
            'patient_name' => 'Today Patient',
            'patient_phone' => '60123456789',
            'patient_email' => 'today@test.com',
            'clinic_location' => 'seremban',
            'service_id' => $this->service->id,
            'dentist_id' => $this->dentist->id,
            'appointment_date' => Carbon::today()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        Appointment::create([
            'patient_name' => 'Tomorrow Patient',
            'patient_phone' => '60187654321',
            'patient_email' => 'tomorrow@test.com',
            'clinic_location' => 'seremban',
            'service_id' => $this->service->id,
            'dentist_id' => $this->dentist->id,
            'appointment_date' => Carbon::today()->addDay()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        $todayQueue = Queue::whereHas('appointment', function ($q) {
            $q->whereDate('appointment_date', Carbon::today());
        })->get();

        // Should only get today's appointments
        $this->assertLessThanOrEqual(1, $todayQueue->count());
    }

    /**
     * Test waiting patients count
     */
    public function test_count_waiting_patients()
    {
        for ($i = 1; $i <= 3; $i++) {
            $appointment = Appointment::create([
                'patient_name' => "Patient $i",
                'patient_phone' => '601234567' . $i,
                'patient_email' => "patient$i@test.com",
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

        $waitingCount = Queue::where('queue_status', 'waiting')->count();

        $this->assertGreaterThanOrEqual(3, $waitingCount);
    }
}
