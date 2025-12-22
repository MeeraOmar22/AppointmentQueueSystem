<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Service;
use Tests\TestCase;

class AppointmentModelTest extends TestCase
{

    /**
     * Test appointment creation
     */
    public function test_appointment_can_be_created()
    {
        Service::create(['id' => 1, 'name' => 'Cleaning', 'description' => 'Clean teeth', 'estimated_duration' => 30, 'duration_minutes' => 30, 'status' => 1]);
        Dentist::create(['id' => 1, 'name' => 'Dr. Test', 'email' => 'dr@test.com', 'status' => true]);

        $appointment = Appointment::create([
            'patient_name' => 'John Doe',
            'patient_phone' => '60123456789',
            'patient_email' => 'john@example.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_id' => 1,
            'dentist_preference' => 'specific',
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        $this->assertNotNull($appointment->id);
        $this->assertEquals('John Doe', $appointment->patient_name);
    }

    /**
     * Test appointment generates visit token
     */
    public function test_appointment_generates_visit_token()
    {
        Service::create(['id' => 1, 'name' => 'Cleaning', 'description' => 'Clean teeth', 'estimated_duration' => 30, 'duration_minutes' => 30, 'status' => 1]);
        Dentist::create(['id' => 1, 'name' => 'Dr. Test', 'email' => 'dr@test.com', 'status' => true]);

        $appointment = Appointment::create([
            'patient_name' => 'Jane Doe',
            'patient_phone' => '60198765432',
            'patient_email' => 'jane@example.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_id' => 1,
            'dentist_preference' => 'specific',
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '11:00',
            'status' => 'booked',
        ]);

        $this->assertNotNull($appointment->visit_token);
        $this->assertNotEmpty($appointment->visit_token);
    }

    /**
     * Test appointment generates visit code
     */
    public function test_appointment_generates_visit_code()
    {
        Service::create(['id' => 1, 'name' => 'Cleaning', 'description' => 'Clean teeth', 'estimated_duration' => 30, 'duration_minutes' => 30, 'status' => 1]);
        Dentist::create(['id' => 1, 'name' => 'Dr. Test', 'email' => 'dr@test.com', 'status' => true]);

        $appointment = Appointment::create([
            'patient_name' => 'Bob Smith',
            'patient_phone' => '60187654321',
            'patient_email' => 'bob@example.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_id' => 1,
            'dentist_preference' => 'specific',
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '14:00',
            'status' => 'booked',
        ]);

        $this->assertNotNull($appointment->visit_code);
        $this->assertNotEmpty($appointment->visit_code);
    }

    /**
     * Test appointment status updates
     */
    public function test_appointment_status_can_be_updated()
    {
        Service::create(['id' => 1, 'name' => 'Cleaning', 'description' => 'Clean teeth', 'estimated_duration' => 30, 'duration_minutes' => 30, 'status' => 1]);
        Dentist::create(['id' => 1, 'name' => 'Dr. Test', 'email' => 'dr@test.com', 'status' => true]);

        $appointment = Appointment::create([
            'patient_name' => 'Alice',
            'patient_phone' => '60123000000',
            'patient_email' => 'alice@example.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_id' => 1,
            'dentist_preference' => 'specific',
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '09:00',
            'status' => 'booked',
        ]);

        $appointment->update(['status' => 'checked_in']);
        $this->assertEquals('checked_in', $appointment->status);
    }

    /**
     * Test appointment timestamps
     */
    public function test_appointment_has_timestamps()
    {
        Service::create(['id' => 1, 'name' => 'Cleaning', 'description' => 'Clean teeth', 'estimated_duration' => 30, 'duration_minutes' => 30, 'status' => 1]);
        Dentist::create(['id' => 1, 'name' => 'Dr. Test', 'email' => 'dr@test.com', 'status' => true]);

        $appointment = Appointment::create([
            'patient_name' => 'Charlie',
            'patient_phone' => '60124000000',
            'patient_email' => 'charlie@example.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_id' => 1,
            'dentist_preference' => 'specific',
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        $this->assertNotNull($appointment->created_at);
        $this->assertNotNull($appointment->updated_at);
    }

    /**
     * Test multiple appointments same date
     */
    public function test_multiple_appointments_on_same_date()
    {
        Service::create(['id' => 1, 'name' => 'Cleaning', 'description' => 'Clean teeth', 'estimated_duration' => 30, 'duration_minutes' => 30, 'status' => 1]);
        Dentist::create(['id' => 1, 'name' => 'Dr. Test', 'email' => 'dr@test.com', 'status' => true]);

        $date = now()->addDay()->toDateString();

        Appointment::create([
            'patient_name' => 'Patient One',
            'patient_phone' => '60125000000',
            'patient_email' => 'p1@example.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_id' => 1,
            'dentist_preference' => 'specific',
            'appointment_date' => $date,
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        Appointment::create([
            'patient_name' => 'Patient Two',
            'patient_phone' => '60126000000',
            'patient_email' => 'p2@example.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_id' => 1,
            'dentist_preference' => 'specific',
            'appointment_date' => $date,
            'appointment_time' => '10:30',
            'status' => 'booked',
        ]);

        $appointments = Appointment::whereDate('appointment_date', $date)->get();
        $this->assertEquals(2, $appointments->count());
    }
}
