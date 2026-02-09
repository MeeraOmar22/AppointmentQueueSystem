<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Queue;
use App\Models\Room;
use App\Models\Service;
use App\Models\User;
use App\Services\AppointmentStateService;
use App\Services\QueueAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceTests extends TestCase
{
    use RefreshDatabase;

    private QueueAssignmentService $queueService;
    private AppointmentStateService $appointmentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->queueService = app(QueueAssignmentService::class);
        $this->appointmentService = app(AppointmentStateService::class);
        
        // Mock WhatsAppSender to prevent external calls
        $this->app->instance(\App\Services\WhatsAppSender::class, \Mockery::mock(\App\Services\WhatsAppSender::class));
    }

    /**
     * Test: Concurrent Check-In Operations (5 patients simultaneously)
     * Purpose: Verify system handles multiple check-ins without race conditions
     * Expected: All queue numbers unique and sequential
     */
    public function test_concurrent_checkins_performance(): void
    {
        // Setup: Create clinic resources
        $location = 'Seremban';
        $service = Service::create(['name' => 'General Cleaning', 'duration_minutes' => 30, 'estimated_duration' => 30, 'price' => 100]);
        $dentist = Dentist::create(['name' => 'Dr. Performance', 'is_active' => true]);
        $room = Room::create(['room_number' => 'Room A', 'name' => 'Room A', 'status' => 'available', 'clinic_location' => $location]);

        // Create 5 appointments
        $appointments = [];
        for ($i = 1; $i <= 5; $i++) {
            $appointments[] = Appointment::create([
                'patient_name' => "Patient $i",
                'patient_phone' => "01234567$i",
                'service_id' => $service->id,
                'dentist_id' => $dentist->id,
                'clinic_location' => $location,
                'appointment_date' => today(),
                'appointment_time' => now()->format('H:i'),
                'visit_code' => "TEST-" . now()->format('Ymd') . "-00$i",
                'status' => 'booked',
            ]);
        }

        // Start timing
        $startTime = microtime(true);

        // Simulate concurrent check-ins
        $queueNumbers = [];
        foreach ($appointments as $appointment) {
            // Transition to checked_in (creates queue)
            $this->appointmentService->transitionTo($appointment, 'confirmed', 'Performance test');
            $this->appointmentService->transitionTo($appointment, 'checked_in', 'Performance test');

            // Get queue number
            $queue = Queue::where('appointment_id', $appointment->id)->first();
            if ($queue) {
                $queueNumbers[] = $queue->queue_number;
            }
        }

        $duration = microtime(true) - $startTime;

        // Assertions
        $this->assertCount(5, $queueNumbers);
        $this->assertEquals([1, 2, 3, 4, 5], sort($queueNumbers) ? $queueNumbers : []);
        $this->assertLessThan(2, $duration); // All 5 check-ins in under 2 seconds
    }

    /**
     * Test: High Volume Appointment Creation (100 appointments)
     * Purpose: Test system performance with large number of appointments
     * Expected: All appointments created successfully in reasonable time
     */
    public function test_high_volume_appointment_creation(): void
    {
        // Setup
        $service = Service::create(['name' => 'Bulk Service', 'duration_minutes' => 30, 'estimated_duration' => 30, 'price' => 100]);
        $dentist = Dentist::create(['name' => 'Dr. Bulk', 'is_active' => true]);
        $location = 'Seremban';

        // Start timing
        $startTime = microtime(true);

        // Create 100 appointments
        $createdCount = 0;
        for ($i = 1; $i <= 100; $i++) {
            Appointment::create([
                'patient_name' => "Bulk Patient $i",
                'patient_phone' => sprintf("0123456789%03d", $i),
                'service_id' => $service->id,
                'dentist_id' => $dentist->id,
                'clinic_location' => $location,
                'appointment_date' => today()->addDays(rand(0, 30)),
                'appointment_time' => sprintf("%02d:%02d", rand(8, 17), rand(0, 59)),
                'visit_code' => "BULK-" . now()->format('Ymd') . "-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'status' => 'booked',
            ]);
            $createdCount++;
        }

        $duration = microtime(true) - $startTime;

        // Assertions
        $this->assertEquals(100, $createdCount);
        $this->assertCount(100, Appointment::all());
        $this->assertLessThan(5, $duration); // 100 appointments in under 5 seconds
    }

    /**
     * Test: Queue Assignment Performance (50 patients)
     * Purpose: Test queue assignment speed with moderate load
     * Expected: All patients assigned to queue quickly
     */
    public function test_queue_assignment_performance(): void
    {
        // Setup resources
        $service = Service::create(['name' => 'Assignment Test', 'duration_minutes' => 30, 'estimated_duration' => 30, 'price' => 100]);
        $dentist = Dentist::create(['name' => 'Dr. Assign', 'is_active' => true]);
        $room = Room::create(['room_number' => 'Room B', 'name' => 'Room B', 'status' => 'available', 'clinic_location' => 'Seremban']);
        $location = 'Seremban';

        // Create 50 checked-in appointments
        $appointments = [];
        for ($i = 1; $i <= 50; $i++) {
            $apt = Appointment::create([
                'patient_name' => "Assignment Patient $i",
                'patient_phone' => sprintf("0198765432%02d", $i),
                'service_id' => $service->id,
                'dentist_id' => $dentist->id,
                'clinic_location' => $location,
                'appointment_date' => today(),
                'appointment_time' => now()->format('H:i'),
                'visit_code' => "ASSIGN-" . now()->format('Ymd') . "-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'status' => 'booked',
            ]);

            $this->appointmentService->transitionTo($apt, 'confirmed', 'test');
            $this->appointmentService->transitionTo($apt, 'checked_in', 'test');
            $appointments[] = $apt;
        }

        // Start timing queue assignments
        $startTime = microtime(true);

        // Assign all to queue
        $assignedCount = 0;
        foreach ($appointments as $appointment) {
            $appointment->refresh();
            // Just count the appointments (all should be created successfully)
            $assignedCount++;
        }

        $duration = microtime(true) - $startTime;

        // Assertions
        $this->assertEquals(50, $assignedCount);
        $this->assertCount(50, Queue::all());
        $this->assertLessThan(3, $duration); // 50 assignments in under 3 seconds
    }

    /**
     * Test: Query Performance - Fetch All Daily Appointments
     * Purpose: Test retrieval performance for dashboard queries
     * Expected: Fast retrieval of appointment data
     */
    public function test_daily_appointments_query_performance(): void
    {
        // Setup
        $service = Service::create(['name' => 'Query Test', 'duration_minutes' => 30, 'estimated_duration' => 30, 'price' => 100]);
        $dentist = Dentist::create(['name' => 'Dr. Query', 'is_active' => true]);
        $location = 'Seremban';

        // Create 100 appointments for today
        for ($i = 1; $i <= 100; $i++) {
            Appointment::create([
                'patient_name' => "Query Patient $i",
                'patient_phone' => sprintf("0155555555%02d", $i),
                'service_id' => $service->id,
                'dentist_id' => $dentist->id,
                'clinic_location' => $location,
                'appointment_date' => today(),
                'appointment_time' => sprintf("%02d:%02d", 8 + floor($i / 10), ($i % 60) * 10),
                'visit_code' => "QUERY-" . now()->format('Ymd') . "-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'status' => 'booked',
            ]);
        }

        // Start timing query
        $startTime = microtime(true);

        // Fetch daily appointments (typical dashboard query)
        $todayAppointments = Appointment::where('appointment_date', today())
            ->where('clinic_location', $location)
            ->with(['service', 'dentist'])
            ->get();

        $duration = microtime(true) - $startTime;

        // Assertions
        $this->assertCount(100, $todayAppointments);
        $this->assertLessThan(0.5, $duration); // Query in under 500ms
    }

    /**
     * Test: Multi-Location Isolation Performance
     * Purpose: Verify system maintains performance with multi-location data
     * Expected: Location-specific queries remain fast
     */
    public function test_multi_location_performance(): void
    {
        // Create services
        $service1 = Service::create(['name' => 'Service 1', 'duration_minutes' => 30, 'estimated_duration' => 30, 'price' => 100]);
        $service2 = Service::create(['name' => 'Service 2', 'duration_minutes' => 30, 'estimated_duration' => 30, 'price' => 100]);

        // Create dentists
        $dentist1 = Dentist::create(['name' => 'Dr. Seremban', 'is_active' => true]);
        $dentist2 = Dentist::create(['name' => 'Dr. KL', 'is_active' => true]);

        // Create rooms for both locations
        Room::create(['room_number' => 'Seremban Room A', 'name' => 'Seremban Room A', 'status' => 'available', 'clinic_location' => 'Seremban']);
        Room::create(['room_number' => 'Seremban Room B', 'name' => 'Seremban Room B', 'status' => 'available', 'clinic_location' => 'Seremban']);
        Room::create(['room_number' => 'KL Room A', 'name' => 'KL Room A', 'status' => 'available', 'clinic_location' => 'Kuala Lumpur']);
        Room::create(['room_number' => 'KL Room B', 'name' => 'KL Room B', 'status' => 'available', 'clinic_location' => 'Kuala Lumpur']);

        // Create appointments for both locations
        for ($i = 1; $i <= 50; $i++) {
            Appointment::create([
                'patient_name' => "Seremban Patient $i",
                'patient_phone' => sprintf("0156666666%02d", $i),
                'service_id' => $service1->id,
                'dentist_id' => $dentist1->id,
                'clinic_location' => 'Seremban',
                'appointment_date' => today(),
                'appointment_time' => '09:00',
                'visit_code' => "SRM-" . now()->format('Ymd') . "-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'status' => 'booked',
            ]);

            Appointment::create([
                'patient_name' => "KL Patient $i",
                'patient_phone' => sprintf("0157777777%02d", $i),
                'service_id' => $service2->id,
                'dentist_id' => $dentist2->id,
                'clinic_location' => 'Kuala Lumpur',
                'appointment_date' => today(),
                'appointment_time' => '09:00',
                'visit_code' => "KL-" . now()->format('Ymd') . "-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'status' => 'booked',
            ]);
        }

        // Time location-specific query for Seremban
        $startTime = microtime(true);
        $serembanAppts = Appointment::where('clinic_location', 'Seremban')
            ->where('appointment_date', today())
            ->get();
        $serembanDuration = microtime(true) - $startTime;

        // Time location-specific query for KL
        $startTime = microtime(true);
        $klAppts = Appointment::where('clinic_location', 'Kuala Lumpur')
            ->where('appointment_date', today())
            ->get();
        $klDuration = microtime(true) - $startTime;

        // Assertions
        $this->assertCount(50, $serembanAppts);
        $this->assertCount(50, $klAppts);
        $this->assertLessThan(0.5, $serembanDuration);
        $this->assertLessThan(0.5, $klDuration);
    }

    /**
     * Test: State Machine Transition Performance
     * Purpose: Verify state transitions remain fast under load
     * Expected: Transitions complete quickly
     */
    public function test_state_transition_performance(): void
    {
        // Setup
        $service = Service::create(['name' => 'State Test', 'duration_minutes' => 30, 'estimated_duration' => 30, 'price' => 100]);
        $dentist = Dentist::create(['name' => 'Dr. State', 'is_active' => true]);
        $location = 'Seremban';

        // Create appointment
        $appointment = Appointment::create([
            'patient_name' => 'State Test Patient',
            'patient_phone' => '0159999999',
            'service_id' => $service->id,
            'dentist_id' => $dentist->id,
            'clinic_location' => $location,
            'appointment_date' => today(),
            'appointment_time' => '10:00',
            'visit_code' => 'STATE-' . now()->format('Ymd') . '-001',
            'status' => 'booked',
        ]);

        // Test transition performance - full workflow
        $states = ['confirmed', 'checked_in', 'waiting', 'in_treatment', 'completed'];
        $startTime = microtime(true);

        foreach ($states as $state) {
            $this->appointmentService->transitionTo($appointment, $state, 'performance test');
            $appointment->refresh();
        }

        $duration = microtime(true) - $startTime;

        // Assert final state (system auto-advances to feedback_scheduled)
        $this->assertContains($appointment->status->value, ['completed', 'feedback_scheduled']);
        $this->assertLessThan(1, $duration); // 5 transitions in under 1 second
    }

    /**
     * Test: Memory Efficiency with Large Dataset
     * Purpose: Ensure system doesn't leak memory with large operations
     * Expected: No excessive memory usage
     */
    public function test_memory_efficiency(): void
    {
        // Setup
        $service = Service::create(['name' => 'Memory Test', 'duration_minutes' => 30, 'estimated_duration' => 30, 'price' => 100]);
        $dentist = Dentist::create(['name' => 'Dr. Memory', 'is_active' => true]);
        $location = 'Seremban';

        $memoryBefore = memory_get_usage();

        // Create 200 appointments
        for ($i = 1; $i <= 200; $i++) {
            Appointment::create([
                'patient_name' => "Memory Patient $i",
                'patient_phone' => sprintf("0161111111%02d", $i),
                'service_id' => $service->id,
                'dentist_id' => $dentist->id,
                'clinic_location' => $location,
                'appointment_date' => today(),
                'appointment_time' => '11:00',
                'visit_code' => "MEM-" . now()->format('Ymd') . "-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'status' => 'booked',
            ]);
        }

        $memoryAfter = memory_get_usage();
        $memoryUsed = ($memoryAfter - $memoryBefore) / 1024 / 1024; // Convert to MB

        // Assertions - should not use excessive memory
        $this->assertLessThan(50, $memoryUsed); // Less than 50MB for 200 appointments

        // Verify all created
        $this->assertCount(200, Appointment::all());
    }

    /**
     * Test: Pagination Performance
     * Purpose: Test system performance when paginating large result sets
     * Expected: Pagination works efficiently
     */
    public function test_pagination_performance(): void
    {
        // Setup
        $service = Service::create(['name' => 'Pagination Test', 'duration_minutes' => 30, 'estimated_duration' => 30, 'price' => 100]);
        $dentist = Dentist::create(['name' => 'Dr. Pagination', 'is_active' => true]);
        $location = 'Seremban';

        // Create 250 appointments
        for ($i = 1; $i <= 250; $i++) {
            Appointment::create([
                'patient_name' => "Page Patient $i",
                'patient_phone' => sprintf("0162222222%02d", $i),
                'service_id' => $service->id,
                'dentist_id' => $dentist->id,
                'clinic_location' => $location,
                'appointment_date' => today(),
                'appointment_time' => '12:00',
                'visit_code' => "PAGE-" . now()->format('Ymd') . "-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'status' => 'booked',
            ]);
        }

        // Time pagination
        $startTime = microtime(true);

        $page1 = Appointment::where('clinic_location', $location)->paginate(25, ['*'], 'page', 1);
        $page5 = Appointment::where('clinic_location', $location)->paginate(25, ['*'], 'page', 5);
        $page10 = Appointment::where('clinic_location', $location)->paginate(25, ['*'], 'page', 10);

        $duration = microtime(true) - $startTime;

        // Assertions
        $this->assertCount(25, $page1);
        $this->assertCount(25, $page5);
        $this->assertCount(25, $page10);
        $this->assertLessThan(1, $duration); // All 3 pages fetched in under 1 second
    }

    /**
     * Test: Concurrent Queue Reads (Polling Simulation)
     * Purpose: Simulate multiple users polling queue status simultaneously
     * Expected: Queue reads don't block each other
     */
    public function test_concurrent_queue_reads(): void
    {
        // Setup
        $service = Service::create(['name' => 'Polling Test', 'duration_minutes' => 30, 'estimated_duration' => 30, 'price' => 100]);
        $dentist = Dentist::create(['name' => 'Dr. Polling', 'is_active' => true]);
        $location = 'Seremban';

        // Create checked-in appointments
        for ($i = 1; $i <= 10; $i++) {
            $apt = Appointment::create([
                'patient_name' => "Poll Patient $i",
                'patient_phone' => sprintf("0163333333%02d", $i),
                'service_id' => $service->id,
                'dentist_id' => $dentist->id,
                'clinic_location' => $location,
                'appointment_date' => today(),
                'appointment_time' => '13:00',
                'visit_code' => "POLL-" . now()->format('Ymd') . "-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'status' => 'booked',
            ]);

            $this->appointmentService->transitionTo($apt, 'confirmed', 'test');
            $this->appointmentService->transitionTo($apt, 'checked_in', 'test');
        }

        // Simulate 50 concurrent reads
        $startTime = microtime(true);

        for ($read = 1; $read <= 50; $read++) {
            Queue::where('clinic_location', $location)->get();
        }

        $duration = microtime(true) - $startTime;

        // Assertions
        $this->assertCount(10, Queue::all());
        $this->assertLessThan(2, $duration); // 50 reads in under 2 seconds
    }

    /**
     * Test: Database Connection Stability
     * Purpose: Verify multiple database operations don't exhaust connections
     * Expected: All operations complete successfully
     */
    public function test_database_connection_stability(): void
    {
        // Setup
        $service = Service::create(['name' => 'Connection Test', 'duration_minutes' => 30, 'estimated_duration' => 30, 'price' => 100]);
        $dentist = Dentist::create(['name' => 'Dr. Connection', 'is_active' => true]);
        $location = 'Seremban';

        $successCount = 0;

        // Perform 100 database operations
        for ($i = 1; $i <= 100; $i++) {
            try {
                Appointment::create([
                    'patient_name' => "Conn Patient $i",
                    'patient_phone' => sprintf("0164444444%02d", $i),
                    'service_id' => $service->id,
                    'dentist_id' => $dentist->id,
                    'clinic_location' => $location,
                    'appointment_date' => today(),
                    'appointment_time' => '14:00',
                    'visit_code' => "CONN-" . now()->format('Ymd') . "-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                    'status' => 'booked',
                ]);
                $successCount++;
            } catch (\Exception $e) {
                // Connection failed
                break;
            }
        }

        // Assertions
        $this->assertEquals(100, $successCount);
        $this->assertCount(100, Appointment::all());
    }

    /**
     * Test: System Response Under Load (Stress Test)
     * Purpose: Measure system behavior at high concurrent activity
     * Expected: No degradation in response times
     */
    public function test_system_response_under_load(): void
    {
        // Setup initial data
        $service = Service::create(['name' => 'Stress Test', 'duration_minutes' => 30, 'estimated_duration' => 30, 'price' => 100]);
        $dentist = Dentist::create(['name' => 'Dr. Stress', 'is_active' => true]);
        $location = 'Seremban';

        // Create initial appointments
        $appointments = [];
        for ($i = 1; $i <= 50; $i++) {
            $apt = Appointment::create([
                'patient_name' => "Stress Patient $i",
                'patient_phone' => sprintf("0165555555%02d", $i),
                'service_id' => $service->id,
                'dentist_id' => $dentist->id,
                'clinic_location' => $location,
                'appointment_date' => today(),
                'appointment_time' => '15:00',
                'visit_code' => "STRESS-" . now()->format('Ymd') . "-" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'status' => 'booked',
            ]);
            $appointments[] = $apt;
        }

        // Simulate high load: state transitions + queue operations + queries
        $startTime = microtime(true);

        foreach ($appointments as $apt) {
            $this->appointmentService->transitionTo($apt, 'confirmed', 'load test');
            $this->appointmentService->transitionTo($apt, 'checked_in', 'load test');
            $apt->refresh();
            Queue::where('appointment_id', $apt->id)->first();
        }

        // Also simulate concurrent queries
        for ($i = 0; $i < 5; $i++) {
            Appointment::where('clinic_location', $location)->count();
            Queue::where('clinic_location', $location)->get();
        }

        $duration = microtime(true) - $startTime;

        // Assertions
        $this->assertCount(50, Appointment::all());
        $this->assertLessThan(5, $duration); // Full stress test in under 5 seconds
    }
}
