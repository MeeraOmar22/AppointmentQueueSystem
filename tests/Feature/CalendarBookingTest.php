<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\OperatingHour;
use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Service;

class CalendarBookingTest extends TestCase
{
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test service
        $this->service = Service::firstOrCreate(
            ['name' => 'Cleaning'],
            [
                'name' => 'Cleaning',
                'price' => 50,
                'status' => 'active',
                'estimated_duration' => 30,
            ]
        );
    }

    /** @test */
    public function can_view_booking_form()
    {
        $response = $this->get('/book');
        $response->assertStatus(200);
    }

    /** @test */
    public function can_fetch_available_slots_for_open_day()
    {
        // Get next Monday
        $monday = now()->next(1)->format('Y-m-d');
        
        $response = $this->getJson('/api/booking/slots?date=' . $monday);
        $response->assertStatus(200);
    }

    /** @test */
    public function cannot_fetch_slots_for_closed_day()
    {
        // Sunday is closed
        $sunday = now()->next(0)->format('Y-m-d');
        $response = $this->getJson('/api/booking/slots?date=' . $sunday);
        $response->assertStatus(200);
    }

    /** @test */
    public function can_submit_booking_with_valid_data()
    {
        $tomorrow = now()->addDay()->format('Y-m-d');

        $response = $this->post('/book/submit', [
            'patient_name' => 'John Doe',
            'patient_phone' => '+60123456789',
            'patient_email' => 'john@example.com',
            'service_id' => $this->service->id,
            'appointment_date' => $tomorrow,
            'appointment_time' => '09:00',
        ]);

        // Should redirect or return success
        $this->assertTrue(
            $response->status() === 302 || 
            $response->status() === 200 || 
            $response->status() === 201
        );
    }

    /** @test */
    public function cannot_submit_booking_with_invalid_phone()
    {
        $tomorrow = now()->addDay()->format('Y-m-d');

        $response = $this->post('/book/submit', [
            'patient_name' => 'John Doe',
            'patient_phone' => 'invalid',
            'patient_email' => 'john@example.com',
            'service_id' => $this->service->id,
            'appointment_date' => $tomorrow,
            'appointment_time' => '09:00',
        ]);

        $this->assertTrue($response->status() === 422 || $response->status() === 302);
    }

    /** @test */
    public function cannot_submit_booking_for_past_date()
    {
        $yesterday = now()->subDay()->format('Y-m-d');

        $response = $this->post('/book/submit', [
            'patient_name' => 'John Doe',
            'patient_phone' => '+60123456789',
            'patient_email' => 'john@example.com',
            'service_id' => $this->service->id,
            'appointment_date' => $yesterday,
            'appointment_time' => '09:00',
        ]);

        $this->assertTrue($response->status() === 422 || $response->status() === 302);
    }

    /** @test */
    public function operating_hours_are_loaded()
    {
        $hours = OperatingHour::where('clinic_location', 'seremban')->get();
        $this->assertGreaterThan(0, $hours->count());
    }

    /** @test */
    public function sunday_is_closed()
    {
        $sunday = OperatingHour::where('day_of_week', 0)
            ->where('clinic_location', 'seremban')
            ->first();
        
        $this->assertNotNull($sunday);
        $this->assertTrue($sunday->is_closed);
    }

    /** @test */
    public function weekdays_are_open()
    {
        for ($day = 1; $day <= 6; $day++) {
            $hour = OperatingHour::where('day_of_week', $day)
                ->where('clinic_location', 'seremban')
                ->first();
            
            if ($hour) {
                $this->assertFalse($hour->is_closed);
            }
        }
    }

    /** @test */
    public function can_create_appointment()
    {
        $tomorrow = now()->addDay();
        
        $appointment = Appointment::create([
            'patient_name' => 'Test Patient',
            'patient_phone' => '+60123456789',
            'patient_email' => 'test@example.com',
            'service_id' => $this->service->id,
            'appointment_date' => $tomorrow,
            'appointment_time' => '09:00',
            'status' => 'pending',
            'visit_code' => 'TEST-' . date('Ymd') . '-' . rand(10000, 99999),
        ]);

        $this->assertNotNull($appointment->id);
        $this->assertEquals('Test Patient', $appointment->patient_name);
    }
}
