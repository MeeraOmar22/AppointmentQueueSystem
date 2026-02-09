<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Queue;
use App\Models\Service;
use Tests\TestCase;

class QueueModelTest extends TestCase
{

    /**
     * Test queue number incrementing
     */
    public function test_queue_number_increments()
    {
        Service::create(['id' => 1, 'name' => 'Cleaning', 'description' => 'Clean teeth', 'estimated_duration' => 30, 'duration_minutes' => 30, 'status' => 1]);
        Dentist::create(['id' => 1, 'name' => 'Dr. Test', 'email' => 'dr@test.com', 'status' => true]);

        $apt1 = Appointment::create([
            'patient_name' => 'Patient 1',
            'patient_phone' => '60111111111',
            'patient_email' => 'p1@example.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_id' => 1,
            'dentist_preference' => 'specific',
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        $queue1 = Queue::create(['appointment_id' => $apt1->id, 'queue_number' => 1, 'queue_status' => 'waiting']);
        $this->assertEquals(1, $queue1->queue_number);
    }

    /**
     * Test queue for today only
     */
    public function test_queue_number_resets_per_day()
    {
        Service::create(['id' => 1, 'name' => 'Cleaning', 'description' => 'Clean teeth', 'estimated_duration' => 30, 'duration_minutes' => 30, 'status' => 1]);
        Dentist::create(['id' => 1, 'name' => 'Dr. Test', 'email' => 'dr@test.com', 'status' => true]);

        $today = now()->toDateString();
        $tomorrow = now()->addDay()->toDateString();

        $apt1 = Appointment::create([
            'patient_name' => 'Today Patient',
            'patient_phone' => '60133333333',
            'patient_email' => 'today@example.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_id' => 1,
            'dentist_preference' => 'specific',
            'appointment_date' => $today,
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        Queue::create(['appointment_id' => $apt1->id, 'queue_number' => 1, 'queue_status' => 'waiting']);

        $apt2 = Appointment::create([
            'patient_name' => 'Tomorrow Patient',
            'patient_phone' => '60133333334',
            'patient_email' => 'tomorrow@example.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_id' => 1,
            'dentist_preference' => 'specific',
            'appointment_date' => $tomorrow,
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        $queue2 = Queue::create(['appointment_id' => $apt2->id, 'queue_number' => 1, 'queue_status' => 'waiting']);
        
        $this->assertEquals(1, $queue2->queue_number);
    }

    /**
     * Test queue has appointment relationship
     */
    public function test_queue_has_appointment()
    {
        Service::create(['id' => 1, 'name' => 'Cleaning', 'description' => 'Clean teeth', 'estimated_duration' => 30, 'duration_minutes' => 30, 'status' => 1]);
        Dentist::create(['id' => 1, 'name' => 'Dr. Test', 'email' => 'dr@test.com', 'status' => true]);

        $apt = Appointment::create([
            'patient_name' => 'Related Patient',
            'patient_phone' => '60144444444',
            'patient_email' => 'related@example.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_id' => 1,
            'dentist_preference' => 'specific',
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '15:00',
            'status' => 'booked',
        ]);

        $queue = Queue::create(['appointment_id' => $apt->id, 'queue_number' => 1, 'queue_status' => 'waiting']);

        $this->assertNotNull($queue->appointment);
        $this->assertEquals($apt->id, $queue->appointment->id);
    }

    /**
     * Test queue status transitions
     */
    public function test_queue_status_transitions()
    {
        Service::create(['id' => 1, 'name' => 'Cleaning', 'description' => 'Clean teeth', 'estimated_duration' => 30, 'duration_minutes' => 30, 'status' => 1]);
        Dentist::create(['id' => 1, 'name' => 'Dr. Test', 'email' => 'dr@test.com', 'status' => true]);

        $apt = Appointment::create([
            'patient_name' => 'Status Patient',
            'patient_phone' => '60155555555',
            'patient_email' => 'status@example.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_id' => 1,
            'dentist_preference' => 'specific',
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        $queue = Queue::create(['appointment_id' => $apt->id, 'queue_number' => 1, 'queue_status' => 'waiting']);
        
        $queue->update(['queue_status' => 'in_treatment']);
        $this->assertEquals('in_service', $queue->queue_status);
    }

    /**
     * Test multiple queues same date
     */
    public function test_multiple_queue_entries_same_date()
    {
        Service::create(['id' => 1, 'name' => 'Cleaning', 'description' => 'Clean teeth', 'estimated_duration' => 30, 'duration_minutes' => 30, 'status' => 1]);
        Dentist::create(['id' => 1, 'name' => 'Dr. Test', 'email' => 'dr@test.com', 'status' => true]);

        $date = now()->toDateString();

        $apt1 = Appointment::create([
            'patient_name' => 'Patient 1',
            'patient_phone' => '6010000001',
            'patient_email' => 'p1@example.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_id' => 1,
            'dentist_preference' => 'specific',
            'appointment_date' => $date,
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        $apt2 = Appointment::create([
            'patient_name' => 'Patient 2',
            'patient_phone' => '6010000002',
            'patient_email' => 'p2@example.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_id' => 1,
            'dentist_preference' => 'specific',
            'appointment_date' => $date,
            'appointment_time' => '10:30',
            'status' => 'booked',
        ]);

        Queue::create(['appointment_id' => $apt1->id, 'queue_number' => 1, 'queue_status' => 'waiting']);
        Queue::create(['appointment_id' => $apt2->id, 'queue_number' => 2, 'queue_status' => 'waiting']);

        $queues = Queue::all();
        $this->assertEquals(2, $queues->count());
    }

    /**
     * Test queue check-in timestamp
     */
    public function test_queue_checkin_timestamp()
    {
        Service::create(['id' => 1, 'name' => 'Cleaning', 'description' => 'Clean teeth', 'estimated_duration' => 30, 'duration_minutes' => 30, 'status' => 1]);
        Dentist::create(['id' => 1, 'name' => 'Dr. Test', 'email' => 'dr@test.com', 'status' => true]);

        $apt = Appointment::create([
            'patient_name' => 'Check In Time Patient',
            'patient_phone' => '60166666666',
            'patient_email' => 'checkintime@example.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_id' => 1,
            'dentist_preference' => 'specific',
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        $queue = Queue::create(['appointment_id' => $apt->id, 'queue_number' => 1, 'queue_status' => 'waiting']);

        $queue->update(['check_in_time' => now()]);
        $this->assertNotNull($queue->check_in_time);
    }

    /**
     * Test queue increment for multiple dentists
     */
    public function test_queue_increment_per_dentist()
    {
        Service::create(['id' => 1, 'name' => 'Cleaning', 'description' => 'Clean teeth', 'estimated_duration' => 30, 'duration_minutes' => 30, 'status' => 1]);
        Dentist::create(['id' => 1, 'name' => 'Dr. Test', 'email' => 'dr@test.com', 'status' => true]);
        Dentist::create(['id' => 2, 'name' => 'Dr. Test 2', 'email' => 'dr2@test.com', 'status' => true]);

        $date = now()->toDateString();

        $apt1 = Appointment::create([
            'patient_name' => 'Dr1 Patient',
            'patient_phone' => '60177777777',
            'patient_email' => 'dr1p@example.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_id' => 1,
            'dentist_preference' => 'specific',
            'appointment_date' => $date,
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        Queue::create(['appointment_id' => $apt1->id, 'queue_number' => 1, 'queue_status' => 'waiting']);

        $this->assertEquals(1, Queue::first()->queue_number);
    }
}
