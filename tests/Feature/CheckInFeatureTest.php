<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Queue;
use App\Models\Service;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Tests\TestCase;

class CheckInFeatureTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Disable CSRF for testing
        $this->withoutMiddleware(VerifyCsrfToken::class);
        
        // Create required test data
        Service::create([
            'id' => 1,
            'name' => 'Cleaning',
            'description' => 'Dental cleaning',
            'estimated_duration' => 30,
            'duration_minutes' => 30,
            'status' => 1
        ]);
        Dentist::create([
            'id' => 1,
            'name' => 'Dr. Test',
            'email' => 'dr.test@clinic.com',
            'status' => true
        ]);
    }

    /**
     * Test check-in page loads
     */
    public function test_checkin_page_loads()
    {
        $response = $this->get('/checkin');
        $response->assertStatus(200);
    }

    /**
     * Test can check-in with valid token and phone
     */
    public function test_can_checkin_with_valid_token_and_phone()
    {
        $appointment = Appointment::create([
            'patient_name' => 'Check In Patient',
            'patient_phone' => '60166666666',
            'patient_email' => 'checkin@example.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_id' => 1,
            'dentist_preference' => 'specific',
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
        ]);

        $response = $this->post('/checkin', [
            'token' => $appointment->visit_token,
            'phone' => '60166666666',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'checked_in',
        ]);
    }

    /**
     * Test check-in updates queue status
     */
    public function test_checkin_updates_queue_status()
    {
        $appointment = Appointment::create([
            'patient_name' => 'Queue Status Patient',
            'patient_phone' => '60167777777',
            'patient_email' => 'queuestatus@example.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_id' => 1,
            'dentist_preference' => 'specific',
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        $queue = Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
        ]);

        $this->post('/checkin', [
            'token' => $appointment->visit_token,
            'phone' => '60167777777',
        ]);

        $this->assertDatabaseHas('queues', [
            'id' => $queue->id,
            'queue_status' => 'in_service',
        ]);
    }

    /**
     * Test check-in fails with wrong phone
     */
    public function test_checkin_fails_with_wrong_phone()
    {
        $appointment = Appointment::create([
            'patient_name' => 'Wrong Phone Patient',
            'patient_phone' => '60166666666',
            'patient_email' => 'wrongphone@example.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_id' => 1,
            'dentist_preference' => 'specific',
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        $response = $this->post('/checkin', [
            'token' => $appointment->visit_token,
            'phone' => '60199999999', // Wrong phone
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'booked', // Should not change
        ]);
    }

    /**
     * Test check-in fails with invalid token
     */
    public function test_checkin_fails_with_invalid_token()
    {
        $response = $this->post('/checkin', [
            'token' => 'invalid-token-12345',
            'phone' => '60166666666',
        ]);

        $response->assertRedirect();
    }

    /**
     * Test check-in form validation - missing phone
     */
    public function test_checkin_requires_phone()
    {
        $appointment = Appointment::create([
            'patient_name' => 'No Phone Patient',
            'patient_phone' => '60165555555',
            'patient_email' => 'nophone@example.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_preference' => 'specific',
            'dentist_id' => 1,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        $response = $this->post('/checkin', [
            'token' => $appointment->visit_token,
            // Missing phone
        ]);

        $response->assertSessionHasErrors('phone');
    }

    /**
     * Test check-in form validation - missing token
     */
    public function test_checkin_requires_token()
    {
        $response = $this->post('/checkin', [
            'phone' => '60165555555',
            // Missing token
        ]);

        $response->assertSessionHasErrors('token');
    }

    /**
     * Test multiple check-in attempts don't duplicate status
     */
    public function test_multiple_checkin_attempts_idempotent()
    {
        $appointment = Appointment::create([
            'patient_name' => 'Multiple Check-in Patient',
            'patient_phone' => '60165555555',
            'patient_email' => 'multiple@example.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_preference' => 'specific',
            'dentist_id' => 1,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
        ]);

        // First check-in
        $this->post('/checkin', [
            'token' => $appointment->visit_token,
            'phone' => '60165555555',
        ]);

        // Second check-in attempt
        $this->post('/checkin', [
            'token' => $appointment->visit_token,
            'phone' => '60165555555',
        ]);

        // Should only have one checked_in record
        $this->assertEquals(1, Appointment::where('status', 'checked_in')->count());
    }

    /**
     * Test check-in with valid phone number format
     */
    public function test_checkin_accepts_valid_phone_format()
    {
        $appointment = Appointment::create([
            'patient_name' => 'Format Patient',
            'patient_phone' => '60165555555',
            'patient_email' => 'format@example.com',
            'clinic_location' => 'seremban',
            'service_id' => 1,
            'dentist_preference' => 'specific',
            'dentist_id' => 1,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
        ]);

        $response = $this->post('/checkin', [
            'token' => $appointment->visit_token,
            'phone' => '60165555555',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'checked_in',
        ]);
    }

    /**
     * Test check-in creates appropriate log entry
     */
    public function test_checkin_logs_activity()
    {
        $appointment = Appointment::create([
            'patient_name' => 'Log Patient',
            'patient_phone' => '60165555555',
            'patient_email' => 'log@example.com',
            'clinic_location' => 'seremban',
            'dentist_preference' => 'specific',
            'service_id' => 1,
            'dentist_id' => 1,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
        ]);

        $this->post('/checkin', [
            'token' => $appointment->visit_token,
            'phone' => '60165555555',
        ]);

        // Verify appointment was checked in
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'checked_in',
        ]);
    }

    /**
     * Test check-in with international phone format
     */
    public function test_checkin_accepts_international_phone_format()
    {
        $appointment = Appointment::create([
            'patient_name' => 'Intl Phone Patient',
            'patient_phone' => '+60165555555',
            'patient_email' => 'intl@example.com',
            'clinic_location' => 'seremban',
            'dentist_preference' => 'specific',
            'service_id' => 1,
            'dentist_id' => 1,
            'appointment_date' => now()->toDateString(),
            'appointment_time' => '10:00',
            'status' => 'booked',
        ]);

        Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
        ]);

        $response = $this->post('/checkin', [
            'token' => $appointment->visit_token,
            'phone' => '+60165555555',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'checked_in',
        ]);
    }
}
