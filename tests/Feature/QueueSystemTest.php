<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Queue;
use App\Models\Service;
use App\Models\Room;
use App\Models\Dentist;
use App\Enums\AppointmentStatus;
use App\Services\AppointmentStateService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Queue System - Core Functionality', function () {
    
    test('Queue table is empty at start', function () {
        expect(Queue::count())->toBe(0);
    });

    test('Can create an appointment', function () {
        $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
        
        $appointment = Appointment::create([
            'patient_name' => 'John Doe',
            'patient_phone' => '555-0001',
            'patient_email' => 'john@example.com',
            'service_id' => $service->id,
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '09:00:00',
            'start_at' => now(),
            'end_at' => now()->addMinutes(30),
            'status' => 'booked',
            'booking_source' => 'public',
        ]);

        expect($appointment->id)->toBeGreaterThan(0);
        expect(Appointment::count())->toBe(1);
    });

    test('Appointment can be checked in', function () {
        $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
        
        $appointment = Appointment::create([
            'patient_name' => 'Jane Smith',
            'patient_phone' => '555-0002',
            'patient_email' => 'jane@example.com',
            'service_id' => $service->id,
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '10:00:00',
            'start_at' => now(),
            'end_at' => now()->addMinutes(30),
            'status' => 'checked_in',
            'booking_source' => 'public',
            'checked_in_at' => now(),
        ]);

        expect($appointment->status->value)->toBe('checked_in');
    });

    test('Can add patient to queue', function () {
        $service = Service::create(['name' => 'Cleaning', 'estimated_duration' => 45]);
        
        $appointment = Appointment::create([
            'patient_name' => 'Bob Johnson',
            'patient_phone' => '555-0003',
            'patient_email' => 'bob@example.com',
            'service_id' => $service->id,
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '11:00:00',
            'start_at' => now(),
            'end_at' => now()->addMinutes(45),
            'status' => 'checked_in',
            'booking_source' => 'public',
            'checked_in_at' => now(),
        ]);

        $queueEntry = Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
            'check_in_time' => now(),
        ]);

        expect($queueEntry->id)->toBeGreaterThan(0);
        expect($queueEntry->queue_status)->toBe('waiting');
        expect(Queue::count())->toBe(1);
    });

    test('Multiple patients maintain queue order', function () {
        $service = Service::create(['name' => 'Service', 'estimated_duration' => 30]);
        
        // Create 3 appointments in queue
        for ($i = 1; $i <= 3; $i++) {
            $appointment = Appointment::create([
                'patient_name' => "Patient $i",
                'patient_phone' => "555-001$i",
                'patient_email' => "patient$i@example.com",
                'service_id' => $service->id,
                'appointment_date' => now()->format('Y-m-d'),
                'appointment_time' => sprintf('%02d:%02d:00', 12, $i),
                'start_at' => now()->addMinutes($i),
                'end_at' => now()->addMinutes($i + 30),
                'status' => 'checked_in',
                'booking_source' => 'public',
                'checked_in_at' => now()->addMinutes($i),
            ]);

            Queue::create([
                'appointment_id' => $appointment->id,
                'queue_number' => $i,
                'queue_status' => 'waiting',
                'check_in_time' => now()->addMinutes($i),
            ]);
        }

        // Verify order
        $numbers = Queue::orderBy('queue_number')->pluck('queue_number')->toArray();
        expect($numbers)->toBe([1, 2, 3]);
        expect(Queue::count())->toBe(3);
    });

    test('Patient status transitions from waiting to in_treatment', function () {
        $service = Service::create(['name' => 'Service', 'estimated_duration' => 30]);
        
        $appointment = Appointment::create([
            'patient_name' => 'Alice Brown',
            'patient_phone' => '555-0004',
            'patient_email' => 'alice@example.com',
            'service_id' => $service->id,
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '13:00:00',
            'start_at' => now(),
            'end_at' => now()->addMinutes(30),
            'status' => 'checked_in',
            'booking_source' => 'public',
            'checked_in_at' => now(),
        ]);

        $queueEntry = Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
            'check_in_time' => now(),
        ]);

        // Dentist calls patient
        $queueEntry->update(['queue_status' => 'in_treatment']);

        expect($queueEntry->fresh()->queue_status)->toBe('in_treatment');
    });

    test('Patient status transitions from in_treatment to completed', function () {
        $service = Service::create(['name' => 'Service', 'estimated_duration' => 30]);
        
        $appointment = Appointment::create([
            'patient_name' => 'Charlie Davis',
            'patient_phone' => '555-0005',
            'patient_email' => 'charlie@example.com',
            'service_id' => $service->id,
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '14:00:00',
            'start_at' => now(),
            'end_at' => now()->addMinutes(30),
            'status' => 'in_treatment',
            'booking_source' => 'public',
        ]);

        $queueEntry = Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'in_treatment',
            'check_in_time' => now(),
        ]);

        // Complete treatment
        $queueEntry->update(['queue_status' => 'completed']);
        $appointment->update(['status' => 'completed']);

        expect($queueEntry->fresh()->queue_status)->toBe('completed');
        expect($appointment->fresh()->status->value)->toBe('completed');
    });

    test('Can count waiting patients', function () {
        $service = Service::create(['name' => 'Service', 'estimated_duration' => 30]);
        
        // Create 5 waiting patients
        for ($i = 1; $i <= 5; $i++) {
            $appointment = Appointment::create([
                'patient_name' => "Waiting $i",
                'patient_phone' => "555-002$i",
                'patient_email' => "waiting$i@example.com",
                'service_id' => $service->id,
                'appointment_date' => now()->format('Y-m-d'),
                'appointment_time' => sprintf('%02d:%02d:00', 15, $i),
                'start_at' => now()->addMinutes($i),
                'end_at' => now()->addMinutes($i + 30),
                'status' => 'checked_in',
                'booking_source' => 'public',
                'checked_in_at' => now()->addMinutes($i),
            ]);

            Queue::create([
                'appointment_id' => $appointment->id,
                'queue_number' => $i,
                'queue_status' => 'waiting',
                'check_in_time' => now()->addMinutes($i),
            ]);
        }

        $waitingCount = Queue::where('queue_status', 'waiting')->count();
        expect($waitingCount)->toBe(5);
    });

    test('Can retrieve next waiting patient', function () {
        $service = Service::create(['name' => 'Service', 'estimated_duration' => 30]);
        
        // Create 3 waiting patients
        for ($i = 1; $i <= 3; $i++) {
            $appointment = Appointment::create([
                'patient_name' => "Next $i",
                'patient_phone' => "555-003$i",
                'patient_email' => "next$i@example.com",
                'service_id' => $service->id,
                'appointment_date' => now()->format('Y-m-d'),
                'appointment_time' => sprintf('%02d:%02d:00', 16, $i),
                'start_at' => now()->addMinutes($i),
                'end_at' => now()->addMinutes($i + 30),
                'status' => 'checked_in',
                'booking_source' => 'public',
                'checked_in_at' => now()->addMinutes($i),
            ]);

            Queue::create([
                'appointment_id' => $appointment->id,
                'queue_number' => $i,
                'queue_status' => 'waiting',
                'check_in_time' => now()->addMinutes($i),
            ]);
        }

        // Get first waiting patient
        $next = Queue::where('queue_status', 'waiting')
            ->orderBy('queue_number')
            ->first();

        expect($next)->not->toBeNull();
        expect($next->queue_number)->toBe(1);
    });

    test('Empty queue returns no next patient', function () {
        Queue::truncate();
        
        $next = Queue::where('queue_status', 'waiting')
            ->orderBy('queue_number')
            ->first();

        expect($next)->toBeNull();
    });

    test('Can count queue statistics', function () {
        $service = Service::create(['name' => 'Service', 'estimated_duration' => 30]);
        
        // Create some waiting
        for ($i = 1; $i <= 2; $i++) {
            $appointment = Appointment::create([
                'patient_name' => "Wait $i",
                'patient_phone' => "555-004$i",
                'patient_email' => "wait$i@example.com",
                'service_id' => $service->id,
                'appointment_date' => now()->format('Y-m-d'),
                'appointment_time' => sprintf('%02d:%02d:00', 17, $i),
                'start_at' => now()->addMinutes($i),
                'end_at' => now()->addMinutes($i + 30),
                'status' => 'checked_in',
                'booking_source' => 'public',
                'checked_in_at' => now()->addMinutes($i),
            ]);

            Queue::create([
                'appointment_id' => $appointment->id,
                'queue_number' => $i,
                'queue_status' => 'waiting',
                'check_in_time' => now()->addMinutes($i),
            ]);
        }

        // Create one in treatment
        $appointment3 = Appointment::create([
            'patient_name' => 'Treating',
            'patient_phone' => '555-0099',
            'patient_email' => 'treating@example.com',
            'service_id' => $service->id,
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '17:30:00',
            'start_at' => now(),
            'end_at' => now()->addMinutes(30),
            'status' => 'in_treatment',
            'booking_source' => 'public',
        ]);

        Queue::create([
            'appointment_id' => $appointment3->id,
            'queue_number' => 3,
            'queue_status' => 'in_treatment',
            'check_in_time' => now(),
        ]);

        $waiting = Queue::where('queue_status', 'waiting')->count();
        $inTreatment = Queue::where('queue_status', 'in_treatment')->count();

        expect($waiting)->toBe(2);
        expect($inTreatment)->toBe(1);
        expect(Queue::count())->toBe(3);
    });

    test('Completed patients are archived', function () {
        $service = Service::create(['name' => 'Service', 'estimated_duration' => 30]);
        
        $appointment = Appointment::create([
            'patient_name' => 'Completed Patient',
            'patient_phone' => '555-0888',
            'patient_email' => 'completed@example.com',
            'service_id' => $service->id,
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '18:00:00',
            'start_at' => now(),
            'end_at' => now()->addMinutes(30),
            'status' => 'completed',
            'booking_source' => 'public',
        ]);

        $queueEntry = Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'completed',
            'check_in_time' => now(),
        ]);

        $completed = Queue::where('queue_status', 'completed')->count();
        $waiting = Queue::where('queue_status', 'waiting')->count();

        expect($completed)->toBe(1);
        expect($waiting)->toBe(0);
    });
});

describe('Queue System - Mandatory Dentist Assignment', function () {
    
    test('Patient cannot enter treatment without dentist', function () {
        // Setup
        $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
        $room = Room::create([
            'room_number' => 'Room 1',
            'capacity' => 1,
            'status' => 'available',
            'is_active' => true,
            'clinic_location' => 'seremban',
        ]);
        // NO dentist available
        
        $appointment = Appointment::create([
            'patient_name' => 'Patient 1',
            'patient_phone' => '555-0001',
            'patient_email' => 'p1@test.com',
            'service_id' => $service->id,
            'dentist_id' => null, // No dentist assigned
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '09:00:00',
            'start_at' => now(),
            'end_at' => now()->addMinutes(30),
            'status' => 'checked_in',
            'booking_source' => 'public',
            'checked_in_at' => now(),
            'clinic_location' => 'seremban',
        ]);

        $queue = Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
            'check_in_time' => now(),
        ]);

        // ACT
        $queueService = new \App\Services\QueueAssignmentService();
        $assigned = $queueService->assignNextPatient('seremban');

        // ASSERT: Patient NOT assigned (no dentist available)
        expect($assigned)->toBeNull();
        expect($queue->refresh()->queue_status)->toBe('waiting');
        expect($appointment->refresh()->status->value)->toBe('checked_in'); // Still checked_in, NOT in_treatment
        expect($appointment->refresh()->dentist_id)->toBeNull(); // Dentist STILL null
    });

    test('Patient cannot enter treatment without room', function () {
        // Setup
        $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
        // NO rooms available
        $dentist = Dentist::create(['name' => 'Dr. Ahmad', 'status' => true]);

        $appointment = Appointment::create([
            'patient_name' => 'Patient 1',
            'patient_phone' => '555-0001',
            'patient_email' => 'p1@test.com',
            'service_id' => $service->id,
            'dentist_id' => $dentist->id,
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '09:00:00',
            'start_at' => now(),
            'end_at' => now()->addMinutes(30),
            'status' => 'checked_in',
            'booking_source' => 'public',
            'checked_in_at' => now(),
            'clinic_location' => 'seremban',
        ]);

        $queue = Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
            'check_in_time' => now(),
        ]);

        // ACT
        $queueService = new \App\Services\QueueAssignmentService();
        $assigned = $queueService->assignNextPatient('seremban');

        // ASSERT: Patient NOT assigned (no room available)
        expect($assigned)->toBeNull();
        expect($queue->refresh()->queue_status)->toBe('waiting');
        expect($appointment->refresh()->status->value)->toBe('checked_in'); // Still checked_in, NOT in_treatment
        expect($appointment->refresh()->room_id)->toBeNull(); // Room STILL null
    });

    test('Dentist is assigned immediately upon call', function () {
        // Setup
        $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
        $room = Room::create([
            'room_number' => 'Room 1',
            'capacity' => 1,
            'status' => 'available',
            'is_active' => true,
            'clinic_location' => 'seremban',
        ]);
        $dentist = Dentist::create(['name' => 'Dr. Ahmad', 'status' => true]);

        $appointment = Appointment::create([
            'patient_name' => 'Patient 1',
            'patient_phone' => '555-0001',
            'patient_email' => 'p1@test.com',
            'service_id' => $service->id,
            'dentist_id' => null, // Not pre-assigned
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '09:00:00',
            'start_at' => now(),
            'end_at' => now()->addMinutes(30),
            'status' => 'waiting',
            'booking_source' => 'public',
            'checked_in_at' => now(),
            'clinic_location' => 'seremban',
        ]);

        $queue = Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
            'check_in_time' => now(),
        ]);

        // ACT
        $queueService = new \App\Services\QueueAssignmentService();
        $assigned = $queueService->assignNextPatient('seremban');

        // ASSERT
        expect($assigned)->not->toBeNull();
        expect($assigned->queue_status)->toBe('in_treatment');
        expect($appointment->refresh()->dentist_id)->toBe($dentist->id); // ✓ Dentist assigned
        expect($assigned->room_id)->toBe($room->id); // ✓ Queue has room_id
        expect($appointment->refresh()->status->value)->toBe('in_treatment'); // ✓ in_treatment status
    });

    test('Dentist status becomes "busy" when assigned to treatment', function () {
        // Setup
        $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
        $room = Room::create([
            'room_number' => 'Room 1',
            'capacity' => 1,
            'status' => 'available',
            'is_active' => true,
            'clinic_location' => 'seremban',
        ]);
        $dentist = Dentist::create(['name' => 'Dr. Ahmad', 'status' => true]); // Initially available

        $appointment = Appointment::create([
            'patient_name' => 'Patient 1',
            'patient_phone' => '555-0001',
            'patient_email' => 'p1@test.com',
            'service_id' => $service->id,
            'dentist_id' => null,
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '09:00:00',
            'start_at' => now(),
            'end_at' => now()->addMinutes(30),
            'status' => 'checked_in',
            'booking_source' => 'public',
            'checked_in_at' => now(),
            'clinic_location' => 'seremban',
        ]);

        $queue = Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
            'check_in_time' => now(),
        ]);

        // ACT
        $queueService = new \App\Services\QueueAssignmentService();
        $assigned = $queueService->assignNextPatient('seremban');

        // ASSERT
        expect($assigned)->not->toBeNull();
        expect($dentist->refresh()->status)->toBe(false); // ✓ Dentist is now BUSY (false)
    });

    test('Room status becomes "occupied" when assigned to treatment', function () {
        // Setup
        $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
        $room = Room::create([
            'room_number' => 'Room 1',
            'capacity' => 1,
            'status' => 'available',
            'is_active' => true,
            'clinic_location' => 'seremban',
        ]);
        $dentist = Dentist::create(['name' => 'Dr. Ahmad', 'status' => true]);

        $appointment = Appointment::create([
            'patient_name' => 'Patient 1',
            'patient_phone' => '555-0001',
            'patient_email' => 'p1@test.com',
            'service_id' => $service->id,
            'dentist_id' => null,
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '09:00:00',
            'start_at' => now(),
            'end_at' => now()->addMinutes(30),
            'status' => 'checked_in',
            'booking_source' => 'public',
            'checked_in_at' => now(),
            'clinic_location' => 'seremban',
        ]);

        $queue = Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
            'check_in_time' => now(),
        ]);

        // ACT
        $queueService = new \App\Services\QueueAssignmentService();
        $assigned = $queueService->assignNextPatient('seremban');

        // ASSERT
        expect($assigned)->not->toBeNull();
        expect($room->refresh()->status)->toBe('occupied'); // ✓ Room is now OCCUPIED
    });

    test('Unassigned dentist never appears for in-treatment patient', function () {
        // Setup
        $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
        $room = Room::create([
            'room_number' => 'Room 1',
            'capacity' => 1,
            'status' => 'available',
            'is_active' => true,
            'clinic_location' => 'seremban',
        ]);
        $dentist = Dentist::create(['name' => 'Dr. Ahmad', 'status' => true]);

        $appointment = Appointment::create([
            'patient_name' => 'Patient 1',
            'patient_phone' => '555-0001',
            'patient_email' => 'p1@test.com',
            'service_id' => $service->id,
            'dentist_id' => null,
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '09:00:00',
            'start_at' => now(),
            'end_at' => now()->addMinutes(30),
            'status' => 'checked_in',
            'booking_source' => 'public',
            'checked_in_at' => now(),
            'clinic_location' => 'seremban',
        ]);

        $queue = Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
            'check_in_time' => now(),
        ]);

        // ACT
        $queueService = new \App\Services\QueueAssignmentService();
        $assigned = $queueService->assignNextPatient('seremban');

        // ASSERT: In-treatment patient MUST have dentist assigned
        expect($assigned)->not->toBeNull(); // Assignment must succeed
        expect($assigned->queue_status)->toBe('in_treatment'); // Queue marked as in treatment
        expect($appointment->refresh()->dentist_id)->toBe($dentist->id); // Dentist assigned
        expect($room->refresh()->status)->toBe('occupied'); // Room marked as occupied
    });

    test('After completion, dentist becomes available again', function () {
        // Setup
        $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
        $room = Room::create([
            'room_number' => 'Room 1',
            'capacity' => 1,
            'status' => 'available',
            'is_active' => true,
            'clinic_location' => 'seremban',
        ]);
        $dentist = Dentist::create(['name' => 'Dr. Ahmad', 'status' => true]);

        $appointment = Appointment::create([
            'patient_name' => 'Patient 1',
            'patient_phone' => '555-0001',
            'patient_email' => 'p1@test.com',
            'service_id' => $service->id,
            'dentist_id' => null,
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '09:00:00',
            'start_at' => now(),
            'end_at' => now()->addMinutes(30),
            'status' => 'waiting',
            'booking_source' => 'public',
            'checked_in_at' => now(),
            'clinic_location' => 'seremban',
        ]);

        $queue = Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
            'check_in_time' => now(),
        ]);

        // ACT: Assign patient
        $queueService = new \App\Services\QueueAssignmentService();
        $assigned = $queueService->assignNextPatient('seremban');

        // Verify dentist is busy
        expect($dentist->refresh()->status)->toBe(false);

        // ACT: Complete treatment
        $queueService->completeTreatment($assigned);

        // ASSERT: Dentist is available again
        expect($dentist->refresh()->status)->toBe(true); // ✓ Back to AVAILABLE
        expect($appointment->refresh()->actual_end_time)->not->toBeNull(); // ✓ End time recorded
        expect($appointment->refresh()->status->value)->toBe('completed'); // ✓ Completed status
    });

    test('Cannot assign multiple patients to same busy dentist', function () {
        // Setup
        $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
        $room1 = Room::create([
            'room_number' => 'Room 1',
            'capacity' => 1,
            'status' => 'available',
            'is_active' => true,
            'clinic_location' => 'seremban',
        ]);
        $room2 = Room::create([
            'room_number' => 'Room 2',
            'capacity' => 1,
            'status' => 'available',
            'is_active' => true,
            'clinic_location' => 'seremban',
        ]);
        $dentist = Dentist::create(['name' => 'Dr. Ahmad', 'status' => true]);

        // Patient 1
        $appointment1 = Appointment::create([
            'patient_name' => 'Patient 1',
            'patient_phone' => '555-0001',
            'patient_email' => 'p1@test.com',
            'service_id' => $service->id,
            'dentist_id' => null,
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '09:00:00',
            'start_at' => now(),
            'end_at' => now()->addMinutes(30),
            'status' => 'checked_in',
            'booking_source' => 'public',
            'checked_in_at' => now(),
            'clinic_location' => 'seremban',
        ]);

        $queue1 = Queue::create([
            'appointment_id' => $appointment1->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
            'check_in_time' => now(),
        ]);

        // Patient 2
        $appointment2 = Appointment::create([
            'patient_name' => 'Patient 2',
            'patient_phone' => '555-0002',
            'patient_email' => 'p2@test.com',
            'service_id' => $service->id,
            'dentist_id' => null,
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '09:10:00',
            'start_at' => now(),
            'end_at' => now()->addMinutes(30),
            'status' => 'checked_in',
            'booking_source' => 'public',
            'checked_in_at' => now()->addSeconds(10),
            'clinic_location' => 'seremban',
        ]);

        $queue2 = Queue::create([
            'appointment_id' => $appointment2->id,
            'queue_number' => 2,
            'queue_status' => 'waiting',
            'check_in_time' => now()->addSeconds(10),
        ]);

        // ACT
        $queueService = new \App\Services\QueueAssignmentService();
        $assigned1 = $queueService->assignNextPatient('seremban');

        // Verify Patient 1 assigned to Dr. Ahmad (only dentist)
        expect($assigned1)->not->toBeNull();
        expect($assigned1->appointment->dentist_id)->toBe($dentist->id);
        expect($dentist->refresh()->status)->toBe(false); // Busy

        // ACT: Try to assign Patient 2 (no dentist available now)
        $assigned2 = $queueService->assignNextPatient('seremban');

        // ASSERT: Patient 2 NOT assigned (dentist busy, no other dentist available)
        expect($assigned2)->toBeNull();
        expect($queue2->refresh()->queue_status)->toBe('waiting'); // Still waiting
        expect($appointment2->refresh()->dentist_id)->toBeNull(); // Dentist NOT assigned
    });
});

describe('Queue System - Room Exclusivity', function () {
    
    test('Two patients cannot share the same room concurrently', function () {
        // CRITICAL: This is the main business rule
        // A room is an exclusive physical resource - only ONE patient can be in it at a time
        
        // Setup
        $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
        $room = Room::create([
            'room_number' => 'Room 1',
            'capacity' => 1,
            'status' => 'available',
            'is_active' => true,
            'clinic_location' => 'seremban',
        ]);
        $dentist1 = Dentist::create(['name' => 'Dr. Ahmad', 'status' => true]);
        $dentist2 = Dentist::create(['name' => 'Dr. Sarah', 'status' => true]);

        // Patient 1: Will be assigned to the room
        $appointment1 = Appointment::create([
            'patient_name' => 'Patient 1',
            'patient_phone' => '555-0001',
            'patient_email' => 'p1@test.com',
            'service_id' => $service->id,
            'dentist_id' => null,
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '09:00:00',
            'start_at' => now(),
            'end_at' => now()->addMinutes(30),
            'status' => 'checked_in',
            'booking_source' => 'public',
            'checked_in_at' => now(),
            'clinic_location' => 'seremban',
        ]);

        $queue1 = Queue::create([
            'appointment_id' => $appointment1->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
            'check_in_time' => now(),
        ]);

        // Patient 2: Waiting, should NOT get same room
        $appointment2 = Appointment::create([
            'patient_name' => 'Patient 2',
            'patient_phone' => '555-0002',
            'patient_email' => 'p2@test.com',
            'service_id' => $service->id,
            'dentist_id' => null,
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '09:30:00',
            'start_at' => now()->addMinutes(30),
            'end_at' => now()->addMinutes(60),
            'status' => 'checked_in',
            'booking_source' => 'public',
            'checked_in_at' => now(),
            'clinic_location' => 'seremban',
        ]);

        $queue2 = Queue::create([
            'appointment_id' => $appointment2->id,
            'queue_number' => 2,
            'queue_status' => 'waiting',
            'check_in_time' => now(),
        ]);

        // ACT: Assign Patient 1
        $queueService = new \App\Services\QueueAssignmentService();
        $assigned1 = $queueService->assignNextPatient('seremban');

        // ASSERT: Patient 1 in treatment with the room
        expect($assigned1)->not->toBeNull();
        expect($assigned1->queue_status)->toBe('in_treatment');
        expect($assigned1->room_id)->toBe($room->id);
        expect($room->refresh()->status)->toBe('occupied');

        // ACT: Try to assign Patient 2
        $assigned2 = $queueService->assignNextPatient('seremban');

        // ASSERT: Patient 2 NOT assigned (room occupied, no other room available)
        expect($assigned2)->toBeNull();
        expect($queue2->refresh()->queue_status)->toBe('waiting');
        expect($queue2->refresh()->room_id)->toBeNull(); // Not assigned to any room
        
        // VERIFY: Only Patient 1 is in the room
        $patientsInRoom = Queue::where('room_id', $room->id)
            ->where('queue_status', 'in_treatment')
            ->count();
        expect($patientsInRoom)->toBe(1); // Only ONE patient in this room
    });

    test('Room becomes available only after treatment completion', function () {
        // Setup
        $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
        $room = Room::create([
            'room_number' => 'Room 1',
            'capacity' => 1,
            'status' => 'available',
            'is_active' => true,
            'clinic_location' => 'seremban',
        ]);
        $dentist = Dentist::create(['name' => 'Dr. Ahmad', 'status' => true]);

        // Patient 1
        $appointment1 = Appointment::create([
            'patient_name' => 'Patient 1',
            'patient_phone' => '555-0001',
            'patient_email' => 'p1@test.com',
            'service_id' => $service->id,
            'dentist_id' => null,
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '09:00:00',
            'start_at' => now(),
            'end_at' => now()->addMinutes(30),
            'status' => 'checked_in',
            'booking_source' => 'public',
            'checked_in_at' => now(),
            'clinic_location' => 'seremban',
        ]);

        $queue1 = Queue::create([
            'appointment_id' => $appointment1->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
            'check_in_time' => now(),
        ]);

        // ACT: Assign Patient 1 to treatment
        $queueService = new \App\Services\QueueAssignmentService();
        $assigned1 = $queueService->assignNextPatient('seremban');

        expect($room->refresh()->status)->toBe('occupied');

        // ACT: Complete Patient 1's treatment
        $queueService->completeTreatment($assigned1);

        // ASSERT: Room becomes available again
        expect($room->refresh()->status)->toBe('available'); // ✓ Back to available
        expect($assigned1->refresh()->queue_status)->toBe('completed');
    });

    test('Multiple rooms allow multiple concurrent patients', function () {
        // Setup
        $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
        $room1 = Room::create([
            'room_number' => 'Room 1',
            'capacity' => 1,
            'status' => 'available',
            'is_active' => true,
            'clinic_location' => 'seremban',
        ]);
        $room2 = Room::create([
            'room_number' => 'Room 2',
            'capacity' => 1,
            'status' => 'available',
            'is_active' => true,
            'clinic_location' => 'seremban',
        ]);
        $dentist1 = Dentist::create(['name' => 'Dr. Ahmad', 'status' => true]);
        $dentist2 = Dentist::create(['name' => 'Dr. Sarah', 'status' => true]);

        // Patient 1
        $appointment1 = Appointment::create([
            'patient_name' => 'Patient 1',
            'patient_phone' => '555-0001',
            'patient_email' => 'p1@test.com',
            'service_id' => $service->id,
            'dentist_id' => null,
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '09:00:00',
            'start_at' => now(),
            'end_at' => now()->addMinutes(30),
            'status' => 'checked_in',
            'booking_source' => 'public',
            'checked_in_at' => now(),
            'clinic_location' => 'seremban',
        ]);

        $queue1 = Queue::create([
            'appointment_id' => $appointment1->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
            'check_in_time' => now(),
        ]);

        // Patient 2
        $appointment2 = Appointment::create([
            'patient_name' => 'Patient 2',
            'patient_phone' => '555-0002',
            'patient_email' => 'p2@test.com',
            'service_id' => $service->id,
            'dentist_id' => null,
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '09:30:00',
            'start_at' => now()->addMinutes(30),
            'end_at' => now()->addMinutes(60),
            'status' => 'checked_in',
            'booking_source' => 'public',
            'checked_in_at' => now(),
            'clinic_location' => 'seremban',
        ]);

        $queue2 = Queue::create([
            'appointment_id' => $appointment2->id,
            'queue_number' => 2,
            'queue_status' => 'waiting',
            'check_in_time' => now(),
        ]);

        // ACT: Assign both patients
        $queueService = new \App\Services\QueueAssignmentService();
        $assigned1 = $queueService->assignNextPatient('seremban');
        $assigned2 = $queueService->assignNextPatient('seremban');

        // ASSERT: Both assigned to DIFFERENT rooms
        expect($assigned1)->not->toBeNull();
        expect($assigned2)->not->toBeNull();
        expect($assigned1->queue_status)->toBe('in_treatment');
        expect($assigned2->queue_status)->toBe('in_treatment');
        expect($assigned1->room_id)->not->toBe($assigned2->room_id); // ✓ DIFFERENT rooms
        
        // Both rooms occupied
        expect($room1->refresh()->status)->toBe('occupied');
        expect($room2->refresh()->status)->toBe('occupied');

        // Verify no room has 2 patients
        $roomsWithMultiple = Queue::where('queue_status', 'in_treatment')
            ->groupBy('room_id')
            ->selectRaw('room_id, COUNT(*) as patient_count')
            ->havingRaw('COUNT(*) > 1')
            ->count();
        expect($roomsWithMultiple)->toBe(0); // ✓ No room has multiple patients
    });

    test('Seeder does not create duplicate room assignments during initialization', function () {
        // Verify that when multiple patients are marked in_treatment during seeding,
        // they don't all get assigned to the same room
        // This was the original bug - the seeder was randomly assigning rooms without checking occupancy
        
        $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
        
        // Create 3 rooms
        for ($i = 1; $i <= 3; $i++) {
            Room::create([
                'room_number' => "Room $i",
                'capacity' => 1,
                'status' => 'available',
                'is_active' => true,
                'clinic_location' => 'seremban',
            ]);
        }
        
        $dentist1 = Dentist::create(['name' => 'Dr. Ahmad', 'status' => true]);
        $dentist2 = Dentist::create(['name' => 'Dr. Sarah', 'status' => true]);
        
        // Create 2 in-treatment patients like the seeder does
        $rooms = Room::where('clinic_location', 'seremban')->get();
        $assignedRoomIds = [];
        
        for ($i = 1; $i <= 2; $i++) {
            $appointment = Appointment::create([
                'patient_name' => "Patient $i",
                'patient_phone' => "555-000$i",
                'patient_email' => "p$i@test.com",
                'service_id' => $service->id,
                'dentist_id' => ($i === 1 ? $dentist1->id : $dentist2->id),
                'appointment_date' => now()->format('Y-m-d'),
                'appointment_time' => sprintf('%02d:%02d:00', 9 + $i, 0),
                'status' => 'in_treatment',
                'booking_source' => 'public',
                'clinic_location' => 'seremban',
            ]);
            
            // Use the same logic as fixed seeder: pick unassigned room
            $availableRoom = $rooms->whereNotIn('id', $assignedRoomIds)->first();
            if ($availableRoom) {
                $assignedRoomIds[] = $availableRoom->id;
                
                Queue::create([
                    'appointment_id' => $appointment->id,
                    'queue_number' => $i,
                    'queue_status' => 'in_treatment',
                    'room_id' => $availableRoom->id,
                    'dentist_id' => ($i === 1 ? $dentist1->id : $dentist2->id),
                    'check_in_time' => now(),
                ]);
            }
        }
        
        // ASSERT: Two patients should be in different rooms
        $inTreatment = Queue::where('queue_status', 'in_treatment')->orderBy('queue_number')->get();
        expect($inTreatment)->toHaveCount(2);
        expect($inTreatment->first()->room_id)->not->toBe($inTreatment->last()->room_id); // Different rooms
    });

    test('Room assignment respects clinic location', function () {
        // Setup
        $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
        
        $roomSeremban = Room::create([
            'room_number' => 'Room 1',
            'capacity' => 1,
            'status' => 'available',
            'is_active' => true,
            'clinic_location' => 'seremban',
        ]);
        
        $roomKualalumpur = Room::create([
            'room_number' => 'Room 1',
            'capacity' => 1,
            'status' => 'available',
            'is_active' => true,
            'clinic_location' => 'kuala_lumpur',
        ]);
        
        $dentist = Dentist::create(['name' => 'Dr. Ahmad', 'status' => true]);

        // Patient for Seremban clinic
        $appointmentSeremban = Appointment::create([
            'patient_name' => 'Patient Seremban',
            'patient_phone' => '555-0001',
            'patient_email' => 'p1@test.com',
            'service_id' => $service->id,
            'dentist_id' => null,
            'appointment_date' => now()->format('Y-m-d'),
            'appointment_time' => '09:00:00',
            'start_at' => now(),
            'end_at' => now()->addMinutes(30),
            'status' => 'checked_in',
            'booking_source' => 'public',
            'checked_in_at' => now(),
            'clinic_location' => 'seremban',
        ]);

        $queueSeremban = Queue::create([
            'appointment_id' => $appointmentSeremban->id,
            'queue_number' => 1,
            'queue_status' => 'waiting',
            'check_in_time' => now(),
        ]);

        // ACT
        $queueService = new \App\Services\QueueAssignmentService();
        $assigned = $queueService->assignNextPatient('seremban');

        // ASSERT: Only Seremban room is assigned
        expect($assigned)->not->toBeNull();
        expect($assigned->room_id)->toBe($roomSeremban->id); // ✓ Correct clinic room
        expect($assigned->room_id)->not->toBe($roomKualalumpur->id);
    });
});
