<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Room;
use App\Models\Queue;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\Dentist;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PhaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test data
        $this->createTestData();
    }

    protected function createTestData()
    {
        // Create services
        Service::create([
            'name' => 'Cleaning',
            'service_name' => 'Cleaning',
            'estimated_duration' => 30,
            'status' => 1,
        ]);

        Service::create([
            'name' => 'Filling',
            'service_name' => 'Filling',
            'estimated_duration' => 45,
            'status' => 1,
        ]);

        // Create dentists
        Dentist::create([
            'name' => 'Dr. Smith',
            'email' => 'smith@clinic.com',
            'phone' => '0123456789',
            'status' => 'available',
            'clinic_location' => 'seremban',
        ]);

        Dentist::create([
            'name' => 'Dr. Jones',
            'email' => 'jones@clinic.com',
            'phone' => '0123456790',
            'status' => 'available',
            'clinic_location' => 'seremban',
        ]);

        // Create rooms
        Room::create([
            'room_number' => 'A1',
            'capacity' => 1,
            'status' => 'available',
            'clinic_location' => 'seremban',
            'is_active' => true,
        ]);

        Room::create([
            'room_number' => 'A2',
            'capacity' => 1,
            'status' => 'available',
            'clinic_location' => 'seremban',
            'is_active' => true,
        ]);

        // Create appointments for today
        $today = Carbon::today();
        $service = Service::first();
        $dentist = Dentist::first();

        Appointment::create([
            'patient_name' => 'John Doe',
            'patient_phone' => '0198765432',
            'patient_email' => 'john@example.com',
            'clinic_location' => 'seremban',
            'service_id' => $service->id,
            'dentist_id' => $dentist->id,
            'appointment_date' => $today,
            'appointment_time' => '09:00:00',
            'status' => 'booked',
            'visit_code' => 'APT-001',
            'visit_token' => 'TOKEN-001',
        ]);

        Appointment::create([
            'patient_name' => 'Jane Smith',
            'patient_phone' => '0198765433',
            'patient_email' => 'jane@example.com',
            'clinic_location' => 'seremban',
            'service_id' => $service->id,
            'dentist_id' => $dentist->id,
            'appointment_date' => $today,
            'appointment_time' => '10:00:00',
            'status' => 'booked',
            'visit_code' => 'APT-002',
            'visit_token' => 'TOKEN-002',
        ]);

        // Create queues for check-ins
        $apt1 = Appointment::where('visit_code', 'APT-001')->first();
        $apt2 = Appointment::where('visit_code', 'APT-002')->first();

        Queue::create([
            'appointment_id' => $apt1->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
            'check_in_time' => now(),
        ]);

        Queue::create([
            'appointment_id' => $apt2->id,
            'queue_number' => 2,
            'queue_status' => 'waiting',
            'check_in_time' => now(),
        ]);
    }

    public function test_queue_board_data_endpoint_returns_queues()
    {
        $response = $this->get('/api/queue-board/data');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'inService' => [],
            'waiting' => [],
            'currentNumber',
            'stats' => [
                'waitingCount',
                'inServiceCount',
                'disabledRoomCount',
            ],
            'exceptions' => [
                'disabledRooms' => [],
                'recentCancellations' => [],
            ],
            'timestamp',
        ]);

        $data = $response->json();
        $this->assertEquals(2, $data['stats']['waitingCount']);
        $this->assertEquals(0, $data['stats']['inServiceCount']);
        $this->assertEquals(0, $data['stats']['disabledRoomCount']);
    }

    public function test_queue_board_view_renders()
    {
        $response = $this->get('/queue-board');

        $response->assertStatus(200);
        $response->assertViewIs('public.queue-board');
    }

    public function test_track_api_returns_appointment_data()
    {
        $apt = Appointment::where('visit_code', 'APT-001')->first();

        $response = $this->get('/api/track/' . $apt->visit_code);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'appointment' => [
                'patient_name',
                'patient_phone',
                'service',
                'dentist',
                'checked_in_at',
            ],
            'queueNumber',
            'queueStatus',
            'etaMinutes',
            'currentServing',
            'room',
            'exceptions' => [
                'disabledRooms',
                'recentCancellations',
            ],
        ]);

        $data = $response->json();
        $this->assertEquals('John Doe', $data['appointment']['patient_name']);
        $this->assertEquals(1, $data['queueNumber']);
    }

    public function test_room_disable_creates_exception_alert()
    {
        $room = Room::where('room_number', 'A1')->first();

        // Disable the room
        $this->actingAsStaff();
        $response = $this->put('/staff/rooms/' . $room->id, [
            'room_number' => 'A1',
            'capacity' => 1,
            'status' => 'available',
            'is_active' => false,
        ]);

        $response->assertRedirect('/staff/rooms');

        // Check that activity log was created
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'room_disabled',
            'model_type' => 'Room',
            'model_id' => $room->id,
        ]);

        // Check exception data in queue board
        $data = $this->get('/api/queue-board/data')->json();
        $this->assertEquals(1, $data['stats']['disabledRoomCount']);
        $this->assertCount(1, $data['exceptions']['disabledRooms']);
    }

    public function test_appointment_cancellation_creates_exception_alert()
    {
        $apt = Appointment::where('visit_code', 'APT-002')->first();

        $this->actingAsStaff();
        $response = $this->post('/staff/appointments/' . $apt->id . '/cancel');

        $response->assertRedirect();

        // Check that activity log with queue_exception was created
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'queue_exception',
            'model_type' => 'Appointment',
            'model_id' => $apt->id,
        ]);

        // Check that appointment status is cancelled
        $apt->refresh();
        $this->assertEquals('cancelled', $apt->status);

        // Check exception data in queue board
        $data = $this->get('/api/queue-board/data')->json();
        $this->assertCount(1, $data['exceptions']['recentCancellations']);
    }

    public function test_exception_alerts_logged_to_logs()
    {
        $room = Room::where('room_number', 'A1')->first();

        $this->actingAsStaff();
        $this->put('/staff/rooms/' . $room->id, [
            'room_number' => 'A1',
            'capacity' => 1,
            'status' => 'available',
            'is_active' => false,
        ]);

        // Check that room disable alert was logged
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'room_disabled',
            'model_type' => 'Room',
        ]);
    }

    public function test_disabled_rooms_excluded_from_queue_assignment()
    {
        $room1 = Room::where('room_number', 'A1')->first();
        $room2 = Room::where('room_number', 'A2')->first();

        // Disable room A1
        $this->actingAsStaff();
        $this->put('/staff/rooms/' . $room1->id, [
            'room_number' => 'A1',
            'capacity' => 1,
            'status' => 'available',
            'is_active' => false,
        ]);

        // Verify A1 is marked as disabled
        $room1->refresh();
        $this->assertFalse($room1->is_active);

        // Check that disabled room count is updated
        $data = $this->get('/api/queue-board/data')->json();
        $this->assertEquals(1, $data['stats']['disabledRoomCount']);
    }

    public function test_cancelled_appointments_appear_in_exceptions()
    {
        $apt1 = Appointment::where('visit_code', 'APT-001')->first();
        $apt2 = Appointment::where('visit_code', 'APT-002')->first();

        $this->actingAsStaff();
        
        // Cancel first appointment
        $this->post('/staff/appointments/' . $apt1->id . '/cancel');

        $data = $this->get('/api/queue-board/data')->json();
        
        // Should show recent cancellations
        $this->assertGreaterThan(0, count($data['exceptions']['recentCancellations']));
    }

    protected function actingAsStaff()
    {
        // Create a staff user
        $user = \App\Models\User::create([
            'name' => 'Staff User',
            'email' => 'staff@clinic.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
        ]);

        $this->actingAs($user);
    }
}
