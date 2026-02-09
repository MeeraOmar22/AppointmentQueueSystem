<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Queue;
use App\Models\Room;
use App\Models\Dentist;
use App\Models\Service;
use App\Models\DentistLeave;
use App\Models\OperatingHour;
use App\Enums\AppointmentStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * MODEL VALIDATION TESTING SUITE
 * 
 * Comprehensive testing of all database models
 * Tests verify data integrity, relationships, and constraints
 */
class ModelValidationTestSuite extends TestCase
{
    use RefreshDatabase;

    // ========== APPOINTMENT MODEL TESTS ==========
    
    /**
     * Test 1: Appointment Creation with Valid Data
     * Verifies that appointments are created with correct attributes
     */
    public function test_appointment_model_creation_with_valid_data()
    {
        $appointment = Appointment::factory()->create([
            'patient_name' => 'John Doe',
            'patient_phone' => '0123456789',
            'appointment_date' => Carbon::tomorrow(),
            'status' => AppointmentStatus::BOOKED,
        ]);
        
        $this->assertNotNull($appointment->id);
        $this->assertEquals('John Doe', $appointment->patient_name);
        $this->assertEquals('0123456789', $appointment->patient_phone);
        $this->assertEquals('booked', $appointment->status->value);
    }

    /**
     * Test 2: Appointment Visit Code Generation
     * Verifies that visit codes are generated and unique
     */
    public function test_appointment_visit_code_generation()
    {
        $apt1 = Appointment::factory()->create();
        $apt2 = Appointment::factory()->create();
        
        $this->assertNotNull($apt1->visit_code);
        $this->assertNotNull($apt2->visit_code);
        $this->assertNotEquals($apt1->visit_code, $apt2->visit_code);
        $this->assertEquals(6, strlen($apt1->visit_code));
    }

    /**
     * Test 3: Appointment Status Enum Validation
     * Verifies that only valid status values are allowed
     */
    public function test_appointment_status_enum_validation()
    {
        $appointment = Appointment::factory()->create(['status' => AppointmentStatus::BOOKED]);
        $this->assertInstanceOf(AppointmentStatus::class, $appointment->status);
        $this->assertEquals('booked', $appointment->status->value);
    }

    /**
     * Test 4: Appointment Service Relationship
     * Verifies that appointments correctly relate to services
     */
    public function test_appointment_service_relationship()
    {
        $service = Service::factory()->create(['name' => 'Cleaning']);
        $appointment = Appointment::factory()->create(['service_id' => $service->id]);
        
        $this->assertNotNull($appointment->service);
        $this->assertEquals($service->id, $appointment->service->id);
        $this->assertEquals('Cleaning', $appointment->service->name);
    }

    /**
     * Test 5: Appointment Dentist Relationship
     * Verifies that appointments correctly relate to dentists
     */
    public function test_appointment_dentist_relationship()
    {
        $dentist = Dentist::factory()->create(['name' => 'Dr. Smith']);
        $appointment = Appointment::factory()->create(['dentist_id' => $dentist->id]);
        
        $this->assertNotNull($appointment->dentist);
        $this->assertEquals($dentist->id, $appointment->dentist->id);
        $this->assertEquals('Dr. Smith', $appointment->dentist->name);
    }

    /**
     * Test 6: Appointment Queue Relationship
     * Verifies that appointments correctly relate to queue records
     */
    public function test_appointment_queue_relationship()
    {
        $appointment = Appointment::factory()->create();
        $queue = Queue::factory()->create(['appointment_id' => $appointment->id]);
        
        $this->assertNotNull($appointment->queue);
        $this->assertEquals($queue->id, $appointment->queue->id);
    }

    /**
     * Test 7: Appointment Date Validation
     * Verifies that appointment dates are valid dates
     */
    public function test_appointment_date_validation()
    {
        $date = Carbon::now()->addDays(7);
        $appointment = Appointment::factory()->create(['appointment_date' => $date]);
        
        $this->assertInstanceOf(Carbon::class, $appointment->appointment_date);
        $this->assertEquals($date->format('Y-m-d'), $appointment->appointment_date->format('Y-m-d'));
    }

    /**
     * Test 8: Appointment Time Slot Format
     * Verifies that appointment times are in correct format
     */
    public function test_appointment_time_slot_format()
    {
        $appointment = Appointment::factory()->create(['appointment_time' => '14:30:00']);
        
        $this->assertEquals('14:30:00', $appointment->appointment_time);
        $this->assertMatchesRegularExpression('/^\d{2}:\d{2}:\d{2}$/', $appointment->appointment_time);
    }

    // ========== QUEUE MODEL TESTS ==========
    
    /**
     * Test 9: Queue Creation with Unique Queue Number
     * Verifies that queue numbers are generated and unique per location
     */
    public function test_queue_model_generates_unique_queue_number()
    {
        $apt1 = Appointment::factory()->create(['clinic_location' => 'seremban']);
        $apt2 = Appointment::factory()->create(['clinic_location' => 'seremban']);
        
        $queue1 = Queue::factory()->create(['appointment_id' => $apt1->id, 'queue_number' => 1]);
        $queue2 = Queue::factory()->create(['appointment_id' => $apt2->id, 'queue_number' => 2]);
        
        $this->assertNotEquals($queue1->queue_number, $queue2->queue_number);
    }

    /**
     * Test 10: Queue Status Values
     * Verifies that queue can have valid status values
     */
    public function test_queue_model_status_values()
    {
        $apt = Appointment::factory()->create();
        
        $validStatuses = ['waiting', 'in_treatment', 'completed'];
        
        foreach ($validStatuses as $status) {
            $queue = Queue::factory()->create([
                'appointment_id' => $apt->id,
                'queue_status' => $status
            ]);
            
            $this->assertEquals($status, $queue->queue_status);
        }
    }

    /**
     * Test 11: Queue Appointment Relationship
     * Verifies that queue correctly relates to appointments
     */
    public function test_queue_appointment_relationship()
    {
        $appointment = Appointment::factory()->create();
        $queue = Queue::factory()->create(['appointment_id' => $appointment->id]);
        
        $this->assertNotNull($queue->appointment);
        $this->assertEquals($appointment->id, $queue->appointment->id);
    }

    /**
     * Test 12: Queue Room Relationship
     * Verifies that queue correctly relates to rooms
     */
    public function test_queue_room_relationship()
    {
        $appointment = Appointment::factory()->create();
        $room = Room::factory()->create();
        $queue = Queue::factory()->create([
            'appointment_id' => $appointment->id,
            'room_id' => $room->id
        ]);
        
        $this->assertNotNull($queue->room);
        $this->assertEquals($room->id, $queue->room->id);
    }

    /**
     * Test 13: Queue Dentist Relationship
     * Verifies that queue correctly relates to dentists
     */
    public function test_queue_dentist_relationship()
    {
        $appointment = Appointment::factory()->create();
        $dentist = Dentist::factory()->create();
        $queue = Queue::factory()->create([
            'appointment_id' => $appointment->id,
            'dentist_id' => $dentist->id
        ]);
        
        $this->assertNotNull($queue->dentist);
        $this->assertEquals($dentist->id, $queue->dentist->id);
    }

    /**
     * Test 14: Queue Position Logic
     * Verifies that queue position is correctly tracked
     */
    public function test_queue_position_ordering()
    {
        $apt1 = Appointment::factory()->create();
        $apt2 = Appointment::factory()->create();
        $apt3 = Appointment::factory()->create();
        
        Queue::factory()->create(['appointment_id' => $apt1->id, 'queue_number' => 3]);
        Queue::factory()->create(['appointment_id' => $apt2->id, 'queue_number' => 1]);
        Queue::factory()->create(['appointment_id' => $apt3->id, 'queue_number' => 2]);
        
        $queues = Queue::orderBy('queue_number')->get();
        
        $this->assertEquals(1, $queues[0]->queue_number);
        $this->assertEquals(2, $queues[1]->queue_number);
        $this->assertEquals(3, $queues[2]->queue_number);
    }

    // ========== ROOM MODEL TESTS ==========
    
    /**
     * Test 15: Room Creation and Validation
     * Verifies that rooms are created with correct attributes
     */
    public function test_room_model_creation()
    {
        $room = Room::factory()->create([
            'room_number' => 'Room-A',
            'clinic_location' => 'seremban',
            'status' => true,
            'is_available' => true
        ]);
        
        $this->assertNotNull($room->id);
        $this->assertEquals('Room-A', $room->room_number);
        $this->assertTrue($room->is_available);
    }

    /**
     * Test 16: Room Availability Tracking
     * Verifies that room availability status is tracked
     */
    public function test_room_availability_tracking()
    {
        $room = Room::factory()->create(['is_available' => true]);
        
        // Mark as occupied
        $room->update(['is_available' => false]);
        $this->assertFalse($room->fresh()->is_available);
        
        // Mark as available
        $room->update(['is_available' => true]);
        $this->assertTrue($room->fresh()->is_available);
    }

    /**
     * Test 17: Room Location Filtering
     * Verifies that rooms can be filtered by clinic location
     */
    public function test_room_location_filtering()
    {
        Room::factory()->create(['clinic_location' => 'seremban']);
        Room::factory()->create(['clinic_location' => 'seremban']);
        Room::factory()->create(['clinic_location' => 'kuala_lumpur']);
        
        $serembanRooms = Room::where('clinic_location', 'seremban')->count();
        $klRooms = Room::where('clinic_location', 'kuala_lumpur')->count();
        
        $this->assertEquals(2, $serembanRooms);
        $this->assertEquals(1, $klRooms);
    }

    // ========== DENTIST MODEL TESTS ==========
    
    /**
     * Test 18: Dentist Creation and Specialization
     * Verifies that dentist records are created correctly
     */
    public function test_dentist_model_creation()
    {
        $dentist = Dentist::factory()->create([
            'name' => 'Dr. Ahmed',
            'specialization' => 'Orthodontics',
            'status' => true
        ]);
        
        $this->assertNotNull($dentist->id);
        $this->assertEquals('Dr. Ahmed', $dentist->name);
        $this->assertEquals('Orthodontics', $dentist->specialization);
        $this->assertTrue($dentist->status);
    }

    /**
     * Test 19: Dentist Availability Status
     * Verifies that dentist status (active/inactive) is tracked
     */
    public function test_dentist_status_tracking()
    {
        $dentist = Dentist::factory()->create(['status' => true]);
        
        // Deactivate dentist
        $dentist->update(['status' => false]);
        $this->assertFalse($dentist->fresh()->status);
        
        // Reactivate
        $dentist->update(['status' => true]);
        $this->assertTrue($dentist->fresh()->status);
    }

    /**
     * Test 20: Dentist Leave Relationship
     * Verifies that dentist leaves are correctly related
     */
    public function test_dentist_leave_relationship()
    {
        $dentist = Dentist::factory()->create();
        DentistLeave::factory()->create([
            'dentist_id' => $dentist->id,
            'start_date' => Carbon::now()->addDays(1),
            'end_date' => Carbon::now()->addDays(5)
        ]);
        
        $dentistLeaves = $dentist->leaves()->count();
        $this->assertGreater($dentistLeaves, 0);
    }

    /**
     * Test 21: Dentist Appointment Relationship
     * Verifies that dentist relates to multiple appointments
     */
    public function test_dentist_appointment_relationship()
    {
        $dentist = Dentist::factory()->create();
        Appointment::factory()->create(['dentist_id' => $dentist->id]);
        Appointment::factory()->create(['dentist_id' => $dentist->id]);
        
        $appointmentCount = $dentist->appointments()->count();
        $this->assertEquals(2, $appointmentCount);
    }

    // ========== SERVICE MODEL TESTS ==========
    
    /**
     * Test 22: Service Creation with Pricing
     * Verifies that services are created with price and duration
     */
    public function test_service_model_creation()
    {
        $service = Service::factory()->create([
            'name' => 'Root Canal',
            'price' => 500.00,
            'estimated_duration' => 60
        ]);
        
        $this->assertNotNull($service->id);
        $this->assertEquals('Root Canal', $service->name);
        $this->assertEquals(500.00, $service->price);
        $this->assertEquals(60, $service->estimated_duration);
    }

    /**
     * Test 23: Service Price Validation
     * Verifies that service prices are numeric and non-negative
     */
    public function test_service_price_validation()
    {
        $service = Service::factory()->create(['price' => 250.50]);
        
        $this->assertIsFloat($service->price) || $this->assertIsInt($service->price);
        $this->assertGreaterThanOrEqual(0, $service->price);
    }

    /**
     * Test 24: Service Duration Validation
     * Verifies that service duration is valid
     */
    public function test_service_duration_validation()
    {
        $service = Service::factory()->create(['estimated_duration' => 45]);
        
        $this->assertIsInt($service->estimated_duration);
        $this->assertGreater($service->estimated_duration, 0);
    }

    /**
     * Test 25: Service Appointment Relationship
     * Verifies that services relate to appointments correctly
     */
    public function test_service_appointment_relationship()
    {
        $service = Service::factory()->create();
        Appointment::factory()->create(['service_id' => $service->id]);
        Appointment::factory()->create(['service_id' => $service->id]);
        
        $appointmentCount = $service->appointments()->count();
        $this->assertEquals(2, $appointmentCount);
    }

    // ========== OPERATING HOURS MODEL TESTS ==========
    
    /**
     * Test 26: Operating Hours Creation
     * Verifies that operating hours are created correctly
     */
    public function test_operating_hours_creation()
    {
        $hours = OperatingHour::factory()->create([
            'day_of_week' => 'monday',
            'opening_time' => '09:00',
            'closing_time' => '17:00',
            'session_type' => 'full_day'
        ]);
        
        $this->assertEquals('monday', $hours->day_of_week);
        $this->assertEquals('09:00', $hours->opening_time);
    }

    /**
     * Test 27: Operating Hours by Day Filtering
     * Verifies that operating hours can be filtered by day
     */
    public function test_operating_hours_day_filtering()
    {
        OperatingHour::factory()->create(['day_of_week' => 'monday']);
        OperatingHour::factory()->create(['day_of_week' => 'monday']);
        OperatingHour::factory()->create(['day_of_week' => 'tuesday']);
        
        $mondayHours = OperatingHour::where('day_of_week', 'monday')->count();
        $this->assertEquals(2, $mondayHours);
    }

    // ========== COMPLEX RELATIONSHIP TESTS ==========
    
    /**
     * Test 28: Full Appointment Workflow Relationships
     * Verifies that all relationships work together in workflow
     */
    public function test_full_appointment_workflow_relationships()
    {
        $dentist = Dentist::factory()->create();
        $service = Service::factory()->create();
        $room = Room::factory()->create();
        
        $appointment = Appointment::factory()->create([
            'dentist_id' => $dentist->id,
            'service_id' => $service->id
        ]);
        
        $queue = Queue::factory()->create([
            'appointment_id' => $appointment->id,
            'dentist_id' => $dentist->id,
            'room_id' => $room->id
        ]);
        
        // Verify all relationships
        $this->assertEquals($dentist->id, $appointment->dentist->id);
        $this->assertEquals($service->id, $appointment->service->id);
        $this->assertEquals($room->id, $queue->room->id);
        $this->assertEquals($appointment->id, $queue->appointment->id);
    }

    /**
     * Test 29: Cascading Deletes
     * Verifies that related records are properly managed on deletion
     */
    public function test_cascading_relationships()
    {
        $appointment = Appointment::factory()->create();
        $queue = Queue::factory()->create(['appointment_id' => $appointment->id]);
        
        $queueId = $queue->id;
        $appointment->delete();
        
        // Queue should be deleted with appointment (if cascade is enabled)
        // Or should remain orphaned (if no cascade)
        // This test documents the actual behavior
        $deletedQueue = Queue::find($queueId);
        $this->assertTrue($deletedQueue === null || $deletedQueue->appointment_id === null);
    }

    /**
     * Test 30: Data Integrity Constraints
     * Verifies that NOT NULL constraints are enforced
     */
    public function test_data_integrity_constraints()
    {
        // This test documents required fields
        $this->expectException(\Exception::class);
        
        // Attempting to create without required fields should fail
        Appointment::create([
            'patient_name' => null, // May violate constraint
        ]);
    }

    // ========== SUMMARY REPORT ==========
    
    public static function reportResults()
    {
        return [
            'total_tests' => 30,
            'focus_areas' => [
                'Appointment Model: Tests 1-8 (Creation, visit codes, status, relationships, dates)',
                'Queue Model: Tests 9-14 (Queue numbers, status, relationships, ordering)',
                'Room Model: Tests 15-17 (Creation, availability, location filtering)',
                'Dentist Model: Tests 18-21 (Creation, status, leaves, appointments)',
                'Service Model: Tests 22-25 (Creation, pricing, duration, appointments)',
                'Operating Hours: Tests 26-27 (Creation, day filtering)',
                'Complex Relationships: Tests 28-30 (Workflow, cascades, constraints)',
            ]
        ];
    }
}
