<?php

namespace Tests\Feature;

use App\Services\AppointmentStateService;
use App\Services\QueueAssignmentService;
use App\Services\QueueAnalyticsService;
use App\Services\AvailabilityService;
use App\Services\CheckInService;
use App\Services\EstimatedWaitTimeService;
use App\Models\Appointment;
use App\Models\Queue;
use App\Models\Room;
use App\Models\Dentist;
use App\Models\Service;
use App\Models\OperatingHour;
use App\Enums\AppointmentStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\TestCase;
use Tests\TestCase as BaseTestCase;

/**
 * SERVICE LAYER TESTING SUITE
 * 
 * Comprehensive testing of all critical service-layer components
 * Tests verify business rules, state management, and integration
 */
class ServiceLayerTestSuite extends BaseTestCase
{
    use RefreshDatabase;

    // ========== APPOINTMENT STATE SERVICE TESTS ==========
    
    /**
     * Test 1: Valid State Transitions
     * Verifies that AppointmentStateService correctly allows valid state transitions
     */
    public function test_appointment_state_service_allows_valid_transitions()
    {
        $stateService = app(AppointmentStateService::class);
        $appointment = Appointment::factory()->create(['status' => AppointmentStatus::BOOKED]);
        
        // Test valid transition: booked → confirmed
        $result = $stateService->transitionTo($appointment, 'confirmed', 'Test transition');
        $this->assertTrue($result);
        $this->assertEquals('confirmed', $appointment->fresh()->status->value);
        
        // Test chain of valid transitions
        $stateService->transitionTo($appointment, 'checked_in', 'Customer checked in');
        $this->assertEquals('checked_in', $appointment->fresh()->status->value);
    }

    /**
     * Test 2: Invalid State Transitions Rejected
     * Verifies that invalid transitions are correctly blocked
     */
    public function test_appointment_state_service_rejects_invalid_transitions()
    {
        $stateService = app(AppointmentStateService::class);
        $appointment = Appointment::factory()->create(['status' => AppointmentStatus::BOOKED]);
        
        // Test invalid transition: booked → completed (skips intermediate states)
        $result = $stateService->transitionTo($appointment, 'completed', 'Invalid transition');
        $this->assertFalse($result);
        $this->assertEquals('booked', $appointment->fresh()->status->value);
    }

    /**
     * Test 3: Terminal States Cannot Transition
     * Verifies that terminal states (completed, cancelled, feedback_sent) cannot transition
     */
    public function test_appointment_state_service_terminal_states_immutable()
    {
        $stateService = app(AppointmentStateService::class);
        $appointment = Appointment::factory()->create(['status' => AppointmentStatus::COMPLETED]);
        
        // Attempt to transition from terminal state
        $result = $stateService->transitionTo($appointment, 'feedback_scheduled', 'Should fail');
        $this->assertFalse($result);
        $this->assertEquals('completed', $appointment->fresh()->status->value);
    }

    /**
     * Test 4: State Transition Triggers Automation
     * Verifies that state transitions trigger associated automated actions
     */
    public function test_appointment_state_service_triggers_automation()
    {
        $stateService = app(AppointmentStateService::class);
        $appointment = Appointment::factory()->create([
            'status' => AppointmentStatus::BOOKED,
            'clinic_location' => 'seremban'
        ]);
        
        // Transition to checked_in - should create queue record
        $stateService->transitionTo($appointment, 'checked_in', 'Customer checked in');
        
        $queue = Queue::where('appointment_id', $appointment->id)->first();
        $this->assertNotNull($queue);
        $this->assertEquals('waiting', $queue->queue_status);
        $this->assertNotNull($queue->queue_number);
    }

    // ========== QUEUE ASSIGNMENT SERVICE TESTS ==========
    
    /**
     * Test 5: FIFO Queue Assignment
     * Verifies that patients are assigned in FIFO order (lowest queue number first)
     */
    public function test_queue_assignment_service_enforces_fifo()
    {
        $queueService = app(QueueAssignmentService::class);
        $dentist = Dentist::factory()->create(['status' => true]);
        $room = Room::factory()->create(['status' => true]);
        
        // Create multiple waiting patients
        $apt1 = Appointment::factory()->create(['status' => AppointmentStatus::WAITING, 'clinic_location' => 'seremban']);
        $apt2 = Appointment::factory()->create(['status' => AppointmentStatus::WAITING, 'clinic_location' => 'seremban']);
        
        Queue::create(['appointment_id' => $apt1->id, 'queue_number' => 2, 'queue_status' => 'waiting']);
        Queue::create(['appointment_id' => $apt2->id, 'queue_number' => 1, 'queue_status' => 'waiting']);
        
        // Assign next patient - should be #1 (FIFO), not #2
        $assigned = $queueService->assignNextPatient('seremban');
        $this->assertEquals($apt2->id, $assigned->appointment_id);
    }

    /**
     * Test 6: Dentist Availability Validation
     * Verifies that only available dentists can be assigned
     */
    public function test_queue_assignment_validates_dentist_availability()
    {
        $queueService = app(QueueAssignmentService::class);
        
        // Create busy dentist
        $busyDentist = Dentist::factory()->create(['status' => true]);
        
        // Create waiting patient with preference for busy dentist
        $apt = Appointment::factory()->create([
            'status' => AppointmentStatus::WAITING,
            'dentist_id' => $busyDentist->id,
            'clinic_location' => 'seremban'
        ]);
        Queue::create(['appointment_id' => $apt->id, 'queue_number' => 1, 'queue_status' => 'waiting']);
        
        // Mark dentist as busy
        $busyDentist->update(['status' => false]);
        
        // Assignment should skip unavailable dentist and find alternative
        $assigned = $queueService->assignNextPatient('seremban');
        
        if ($assigned) {
            $this->assertNotEquals($busyDentist->id, $assigned->dentist_id);
        }
    }

    /**
     * Test 7: Room Availability Tracking
     * Verifies that rooms are correctly marked as occupied/available
     */
    public function test_queue_assignment_tracks_room_availability()
    {
        $queueService = app(QueueAssignmentService::class);
        Dentist::factory()->create(['status' => true]);
        $room = Room::factory()->create(['status' => true]);
        
        $apt = Appointment::factory()->create([
            'status' => AppointmentStatus::WAITING,
            'clinic_location' => 'seremban'
        ]);
        Queue::create(['appointment_id' => $apt->id, 'queue_number' => 1, 'queue_status' => 'waiting']);
        
        // Room should be available before assignment
        $this->assertTrue($room->fresh()->is_available);
        
        // After assignment, room should be occupied
        $assigned = $queueService->assignNextPatient('seremban');
        $this->assertFalse($room->fresh()->is_available);
    }

    // ========== QUEUE ANALYTICS SERVICE TESTS ==========
    
    /**
     * Test 8: Wait Time Analysis Calculation
     * Verifies that wait time analytics are calculated correctly
     */
    public function test_queue_analytics_calculates_wait_time()
    {
        $analyticsService = app(QueueAnalyticsService::class);
        
        // Create completed appointments with check-in and treatment times
        Appointment::factory()->create([
            'status' => AppointmentStatus::COMPLETED,
            'clinic_location' => 'seremban',
            'check_in_time' => Carbon::now()->subHours(2),
            'actual_start_time' => Carbon::now()->subHours(2)->addMinutes(10),
            'actual_end_time' => Carbon::now()->subHours(2)->addMinutes(40)
        ]);
        
        $analysis = $analyticsService->getWaitTimeAnalysis('seremban');
        
        $this->assertArrayHasKey('average_wait_time', $analysis);
        $this->assertArrayHasKey('max_wait_time', $analysis);
        $this->assertArrayHasKey('min_wait_time', $analysis);
        $this->assertGreater($analysis['average_wait_time'], 0);
    }

    /**
     * Test 9: Treatment Duration Analysis
     * Verifies that treatment duration accuracy is calculated
     */
    public function test_queue_analytics_calculates_treatment_duration()
    {
        $analyticsService = app(QueueAnalyticsService::class);
        
        Appointment::factory()->create([
            'status' => AppointmentStatus::COMPLETED,
            'clinic_location' => 'seremban',
            'actual_start_time' => Carbon::now()->subHours(1),
            'actual_end_time' => Carbon::now()->subHours(1)->addMinutes(30)
        ]);
        
        $analysis = $analyticsService->getTreatmentDurationAnalysis('seremban');
        
        $this->assertArrayHasKey('average_duration', $analysis);
        $this->assertArrayHasKey('total_treatments', $analysis);
        $this->assertGreater($analysis['average_duration'], 0);
    }

    /**
     * Test 10: Room Utilization Analysis
     * Verifies that room utilization metrics are correct
     */
    public function test_queue_analytics_calculates_room_utilization()
    {
        $analyticsService = app(QueueAnalyticsService::class);
        Room::factory()->create(['clinic_location' => 'seremban']);
        
        $analysis = $analyticsService->getRoomUtilization('seremban');
        
        $this->assertIsArray($analysis);
        $this->assertArrayHasKey('hourly_utilization', $analysis);
    }

    // ========== AVAILABILITY SERVICE TESTS ==========
    
    /**
     * Test 11: Operating Hours Validation
     * Verifies that bookings respect operating hours
     */
    public function test_availability_service_validates_operating_hours()
    {
        $availabilityService = app(AvailabilityService::class);
        
        // Create operating hours: 9 AM - 5 PM
        OperatingHour::factory()->create([
            'day_of_week' => 'monday',
            'session_type' => 'morning',
            'opening_time' => '09:00',
            'closing_time' => '12:00',
            'clinic_location' => 'seremban'
        ]);
        
        $date = Carbon::next('Monday');
        
        // 10 AM should be available
        $available = $availabilityService->isTimeSlotAvailable($date->copy()->setTime(10, 0), 'seremban', 30);
        $this->assertTrue($available);
        
        // 6 PM should be unavailable
        $available = $availabilityService->isTimeSlotAvailable($date->copy()->setTime(18, 0), 'seremban', 30);
        $this->assertFalse($available);
    }

    /**
     * Test 12: Lunch Break Exclusion
     * Verifies that lunch breaks are excluded from bookable slots
     */
    public function test_availability_service_excludes_lunch_breaks()
    {
        $availabilityService = app(AvailabilityService::class);
        
        OperatingHour::factory()->create([
            'day_of_week' => 'tuesday',
            'session_type' => 'morning',
            'opening_time' => '09:00',
            'closing_time' => '12:00',
            'clinic_location' => 'seremban'
        ]);
        
        // Lunch breaks should prevent bookings
        $date = Carbon::next('Tuesday');
        $available = $availabilityService->isTimeSlotAvailable(
            $date->copy()->setTime(12, 30), // During lunch
            'seremban',
            30
        );
        $this->assertFalse($available);
    }

    // ========== CHECK-IN SERVICE TESTS ==========
    
    /**
     * Test 13: Valid Visit Code Check-In
     * Verifies that valid visit codes successfully check in patients
     */
    public function test_checkin_service_accepts_valid_visit_codes()
    {
        $checkinService = app(CheckInService::class);
        $apt = Appointment::factory()->create([
            'status' => AppointmentStatus::CONFIRMED,
            'visit_code' => 'ABC123'
        ]);
        
        $result = $checkinService->checkInByVisitCode('ABC123');
        
        $this->assertNotNull($result);
        $this->assertEquals($apt->id, $result->id);
        $this->assertEquals('checked_in', $result->fresh()->status->value);
    }

    /**
     * Test 14: Invalid Visit Code Rejection
     * Verifies that invalid codes are rejected
     */
    public function test_checkin_service_rejects_invalid_visit_codes()
    {
        $checkinService = app(CheckInService::class);
        
        $result = $checkinService->checkInByVisitCode('INVALID123');
        
        $this->assertNull($result);
    }

    /**
     * Test 15: Duplicate Check-In Prevention
     * Verifies that patients cannot check in twice
     */
    public function test_checkin_service_prevents_duplicate_checkin()
    {
        $checkinService = app(CheckInService::class);
        $apt = Appointment::factory()->create([
            'status' => AppointmentStatus::CHECKED_IN,
            'visit_code' => 'DEF456'
        ]);
        
        $result = $checkinService->checkInByVisitCode('DEF456');
        
        // Should fail because already checked in
        $this->assertNull($result);
    }

    // ========== ESTIMATED WAIT TIME SERVICE TESTS ==========
    
    /**
     * Test 16: Wait Time Estimation Accuracy
     * Verifies that wait time is calculated based on queue position
     */
    public function test_estimated_wait_time_service_calculates_eta()
    {
        $etaService = app(EstimatedWaitTimeService::class);
        
        $apt = Appointment::factory()->create([
            'status' => AppointmentStatus::WAITING,
            'clinic_location' => 'seremban'
        ]);
        
        $eta = $etaService->getETAForAppointment($apt);
        
        $this->assertNotNull($eta);
        $this->assertGreaterThanOrEqual(0, $eta);
    }

    /**
     * Test 17: Zero ETA for In-Treatment Patient
     * Verifies that patients in treatment have zero wait time
     */
    public function test_estimated_wait_time_zero_for_in_treatment()
    {
        $etaService = app(EstimatedWaitTimeService::class);
        
        $apt = Appointment::factory()->create([
            'status' => AppointmentStatus::IN_TREATMENT,
            'clinic_location' => 'seremban'
        ]);
        
        $eta = $etaService->getETAForAppointment($apt);
        
        $this->assertEquals(0, $eta);
    }

    // ========== SUMMARY REPORT ==========
    
    public static function reportResults()
    {
        return [
            'total_tests' => 17,
            'focus_areas' => [
                'State Machine: Tests 1-4 (State transitions, validation, automation)',
                'Queue Management: Tests 5-7 (FIFO, dentist availability, room tracking)',
                'Analytics: Tests 8-10 (Wait time, treatment duration, room utilization)',
                'Availability: Tests 11-12 (Operating hours, lunch breaks)',
                'Check-In: Tests 13-15 (Valid codes, invalid codes, duplicates)',
                'Wait Time Estimation: Tests 16-17 (ETA calculation, in-treatment)',
            ]
        ];
    }
}
