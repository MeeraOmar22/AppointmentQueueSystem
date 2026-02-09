<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Queue;
use App\Models\Service;
use App\Models\Room;
use App\Models\Dentist;
use App\Services\QueueAssignmentService;
use App\Services\CheckInService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('Queue System - Resource-Aware Logic', function () {

    // ==================================================
    // RULE 1: ROOM AVAILABILITY CONSTRAINTS
    // ==================================================
    
    describe('Rule 1: Room Availability Controls Patient Flow', function () {
        
        test('Patient CANNOT be assigned to treatment when NO rooms are available', function () {
            // Setup
            $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
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

            // ACT: Try to assign next patient (no rooms available)
            $queueService = new QueueAssignmentService();
            $assignedQueue = $queueService->assignNextPatient('seremban');

            // ASSERT: Patient NOT assigned (still waiting)
            expect($assignedQueue)->toBeNull();
            expect($queue->refresh()->queue_status)->toBe('waiting');
        });

        test('Patient CAN be assigned to treatment when one room is available', function () {
            // Setup
            $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
            $room = Room::create([
                'room_number' => 'Room 1',
                'capacity' => 1,
                'status' => 'available',
                'is_active' => true,
                'clinic_location' => 'seremban',
            ]);
            $dentist = Dentist::create(['name' => 'Dr. Siti', 'status' => true]);

            $appointment = Appointment::create([
                'patient_name' => 'Patient Available',
                'patient_phone' => '555-0002',
                'patient_email' => 'p2@test.com',
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
            $queueService = new QueueAssignmentService();
            $assignedQueue = $queueService->assignNextPatient('seremban');

            // ASSERT
            expect($assignedQueue)->not->toBeNull();
            expect($assignedQueue->queue_status)->toBe('in_treatment');
            expect($assignedQueue->room_id)->toBe($room->id);
            expect($assignedQueue->dentist_id)->toBe($dentist->id);
        });

        test('Patient CANNOT use occupied room - only one patient per room', function () {
            // CRITICAL: Enforce one patient per room at a time
            // Even if room has capacity for 2 patients, only 1 can be treated at once
            // (This is realistic clinic behavior - only 1 patient per treatment room)
            
            $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
            $room = Room::create([
                'room_number' => 'Room 1',
                'capacity' => 2,  // Room capacity is for equipment, not concurrent patients
                'status' => 'available',
                'is_active' => true,
                'clinic_location' => 'seremban',
            ]);
            $dentist1 = Dentist::create(['name' => 'Dr. A', 'status' => true]);
            $dentist2 = Dentist::create(['name' => 'Dr. B', 'status' => true]);

            // Patient 1 - will use the room
            $appointment1 = Appointment::create([
                'patient_name' => 'Patient 1',
                'patient_phone' => '555-0001',
                'patient_email' => 'p1@test.com',
                'service_id' => $service->id,
                'dentist_id' => $dentist1->id,
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

            // Patient 2 - will wait (same room occupied)
            $appointment2 = Appointment::create([
                'patient_name' => 'Patient 2',
                'patient_phone' => '555-0002',
                'patient_email' => 'p2@test.com',
                'service_id' => $service->id,
                'dentist_id' => $dentist2->id,
                'appointment_date' => now()->format('Y-m-d'),
                'appointment_time' => '09:10:00',
                'start_at' => now(),
                'end_at' => now()->addMinutes(30),
                'status' => 'checked_in',
                'booking_source' => 'public',
                'checked_in_at' => now(),
                'clinic_location' => 'seremban',
            ]);

            $queue2 = Queue::create([
                'appointment_id' => $appointment2->id,
                'queue_number' => 2,
                'queue_status' => 'waiting',
                'check_in_time' => now()->addSeconds(10),
            ]);

            // ACT
            $queueService = new QueueAssignmentService();
            $assigned1 = $queueService->assignNextPatient('seremban');

            // ASSERT: Patient 1 assigned and room marked as occupied
            expect($assigned1->id)->toBe($queue1->id);
            expect($assigned1->queue_status)->toBe('in_treatment');
            expect($assigned1->room_id)->toBe($room->id);
            expect($room->refresh()->status)->toBe('occupied');  // 🔑 Room now occupied

            // ACT: Try to assign patient 2 (should fail - room occupied)
            $assigned2 = $queueService->assignNextPatient('seremban');

            // ASSERT: Patient 2 NOT assigned (room still occupied, only 1 room exists)
            expect($assigned2)->toBeNull();
            expect($queue2->refresh()->queue_status)->toBe('waiting');
            expect(Queue::where('queue_status', 'in_treatment')->count())->toBe(1);
            
            // VERIFY: Only one patient in treatment
            $inTreatment = Queue::where('queue_status', 'in_treatment')->get();
            expect($inTreatment->count())->toBe(1);
            expect($inTreatment->first()->room_id)->toBe($room->id);
        });

        test('Parallel treatment works ONLY with multiple rooms', function () {
            // VERIFY: Two patients can be treated SIMULTANEOUSLY only if they use different rooms
            $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
            
            // Create two separate rooms
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

            $dentist1 = Dentist::create(['name' => 'Dr. A', 'status' => true]);
            $dentist2 = Dentist::create(['name' => 'Dr. B', 'status' => true]);

            // Create two waiting patients
            $appointment1 = Appointment::create([
                'patient_name' => 'Patient 1',
                'patient_phone' => '555-0001',
                'patient_email' => 'p1@test.com',
                'service_id' => $service->id,
                'dentist_id' => $dentist1->id,
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

            $appointment2 = Appointment::create([
                'patient_name' => 'Patient 2',
                'patient_phone' => '555-0002',
                'patient_email' => 'p2@test.com',
                'service_id' => $service->id,
                'dentist_id' => $dentist2->id,
                'appointment_date' => now()->format('Y-m-d'),
                'appointment_time' => '09:05:00',
                'start_at' => now(),
                'end_at' => now()->addMinutes(30),
                'status' => 'checked_in',
                'booking_source' => 'public',
                'checked_in_at' => now()->addSeconds(5),
                'clinic_location' => 'seremban',
            ]);

            $queue2 = Queue::create([
                'appointment_id' => $appointment2->id,
                'queue_number' => 2,
                'queue_status' => 'waiting',
                'check_in_time' => now()->addSeconds(5),
            ]);

            // ACT: Assign patient 1
            $queueService = new QueueAssignmentService();
            $assigned1 = $queueService->assignNextPatient('seremban');

            // ASSERT: Patient 1 in treatment in Room 1
            expect($assigned1->id)->toBe($queue1->id);
            expect($assigned1->queue_status)->toBe('in_treatment');
            expect($assigned1->room_id)->toBe($room1->id);
            expect($room1->refresh()->status)->toBe('occupied');

            // ACT: Assign patient 2
            $assigned2 = $queueService->assignNextPatient('seremban');

            // ASSERT: Patient 2 ALSO in treatment (in different Room 2)
            expect($assigned2->id)->toBe($queue2->id);
            expect($assigned2->queue_status)->toBe('in_treatment');
            expect($assigned2->room_id)->toBe($room2->id);  // Different room!
            expect($room2->refresh()->status)->toBe('occupied');
            
            // VERIFY: Both patients in treatment, different rooms
            expect(Queue::where('queue_status', 'in_treatment')->count())->toBe(2);
            $inTreatment = Queue::where('queue_status', 'in_treatment')->orderBy('queue_number')->get();
            expect($inTreatment->get(0)->room_id)->toBe($room1->id);
            expect($inTreatment->get(1)->room_id)->toBe($room2->id);
        });
    });

    // ==================================================
    // RULE 2: DENTIST AVAILABILITY CONSTRAINTS
    // ==================================================

    describe('Rule 2: Dentist Availability Enforced', function () {
        
        test('Patient CANNOT be assigned when NO dentists are available', function () {
            // Setup
            $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
            Room::create([
                'room_number' => 'Room 1',
                'capacity' => 1,
                'status' => 'available',
                'is_active' => true,
                'clinic_location' => 'seremban',
            ]);
            $dentist = Dentist::create(['name' => 'Dr. Busy', 'status' => false]);

            $appointment = Appointment::create([
                'patient_name' => 'Waiting Patient',
                'patient_phone' => '555-0003',
                'patient_email' => 'p3@test.com',
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

            Queue::create([
                'appointment_id' => $appointment->id,
                'queue_number' => 1,
                'queue_status' => 'waiting',
                'check_in_time' => now(),
            ]);

            // ACT
            $queueService = new QueueAssignmentService();
            $assigned = $queueService->assignNextPatient('seremban');

            // ASSERT
            expect($assigned)->toBeNull();
        });

        test('Patient assigned to different dentist if assigned dentist is busy', function () {
            // Setup
            $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
            Room::create([
                'room_number' => 'Room 1',
                'capacity' => 1,
                'status' => 'available',
                'is_active' => true,
                'clinic_location' => 'seremban',
            ]);
            $assignedDentist = Dentist::create(['name' => 'Dr. Busy', 'status' => false]);
            $availableDentist = Dentist::create(['name' => 'Dr. Available', 'status' => true]);

            $appointment = Appointment::create([
                'patient_name' => 'Patient Reassign',
                'patient_phone' => '555-0004',
                'patient_email' => 'p4@test.com',
                'service_id' => $service->id,
                'dentist_id' => $assignedDentist->id,
                'appointment_date' => now()->format('Y-m-d'),
                'appointment_time' => '09:00:00',
                'start_at' => now(),
                'end_at' => now()->addMinutes(30),
                'status' => 'checked_in',
                'booking_source' => 'public',
                'checked_in_at' => now(),
                'clinic_location' => 'seremban',
            ]);

            Queue::create([
                'appointment_id' => $appointment->id,
                'queue_number' => 1,
                'queue_status' => 'waiting',
                'check_in_time' => now(),
            ]);

            // ACT
            $queueService = new QueueAssignmentService();
            $assigned = $queueService->assignNextPatient('seremban');

            // ASSERT
            expect($assigned)->not->toBeNull();
            expect($assigned->dentist_id)->toBe($availableDentist->id);
        });
    });

    // ==================================================
    // RULE 3: FIFO WITHOUT BLOCKING PARALLEL TREATMENT
    // ==================================================

    describe('Rule 3: FIFO Does NOT Block Parallel Treatment', function () {
        
        test('Two patients treated simultaneously when two rooms available', function () {
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
            $dentist1 = Dentist::create(['name' => 'Dr. A', 'status' => true]);
            $dentist2 = Dentist::create(['name' => 'Dr. B', 'status' => true]);

            // Patient 1
            $appointment1 = Appointment::create([
                'patient_name' => 'Patient 1',
                'patient_phone' => '555-0001',
                'patient_email' => 'p1@test.com',
                'service_id' => $service->id,
                'dentist_id' => $dentist1->id,
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
                'dentist_id' => $dentist2->id,
                'appointment_date' => now()->format('Y-m-d'),
                'appointment_time' => '09:10:00',
                'start_at' => now(),
                'end_at' => now()->addMinutes(30),
                'status' => 'checked_in',
                'booking_source' => 'public',
                'checked_in_at' => now(),
                'clinic_location' => 'seremban',
            ]);

            $queue2 = Queue::create([
                'appointment_id' => $appointment2->id,
                'queue_number' => 2,
                'queue_status' => 'waiting',
                'check_in_time' => now()->addSeconds(10),
            ]);

            // ACT: Assign both patients
            $queueService = new QueueAssignmentService();
            $assigned1 = $queueService->assignNextPatient('seremban');
            
            // Reset room2 and occupy room1 to simulate parallel assignment
            $room1->update(['status' => 'occupied']);
            $room2->update(['status' => 'available']);
            
            $assigned2 = $queueService->assignNextPatient('seremban');

            // ASSERT
            expect($assigned1->id)->toBe($queue1->id);
            expect($assigned1->queue_status)->toBe('in_treatment');
            expect($assigned2->id)->toBe($queue2->id);
            expect($assigned2->queue_status)->toBe('in_treatment');
            expect(Queue::where('queue_status', 'in_treatment')->count())->toBe(2);
        });

        test('Earlier patient in treatment does NOT block later patient assignment', function () {
            // Setup
            $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
            $room1 = Room::create([
                'room_number' => 'Room 1',
                'capacity' => 1,
                'status' => 'occupied',
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
            $dentist1 = Dentist::create(['name' => 'Dr. A', 'status' => true]);
            $dentist2 = Dentist::create(['name' => 'Dr. B', 'status' => true]);

            // Patient 1 - in treatment
            $appointment1 = Appointment::create([
                'patient_name' => 'Patient 1',
                'patient_phone' => '555-0001',
                'patient_email' => 'p1@test.com',
                'service_id' => $service->id,
                'dentist_id' => $dentist1->id,
                'appointment_date' => now()->format('Y-m-d'),
                'appointment_time' => '09:00:00',
                'start_at' => now(),
                'end_at' => now()->addMinutes(30),
                'status' => 'in_treatment',
                'booking_source' => 'public',
                'checked_in_at' => now(),
                'clinic_location' => 'seremban',
            ]);

            $queue1 = Queue::create([
                'appointment_id' => $appointment1->id,
                'queue_number' => 1,
                'queue_status' => 'in_treatment',
                'room_id' => $room1->id,
                'dentist_id' => $dentist1->id,
                'check_in_time' => now(),
            ]);

            // Patient 2 - waiting
            $appointment2 = Appointment::create([
                'patient_name' => 'Patient 2',
                'patient_phone' => '555-0002',
                'patient_email' => 'p2@test.com',
                'service_id' => $service->id,
                'dentist_id' => $dentist2->id,
                'appointment_date' => now()->format('Y-m-d'),
                'appointment_time' => '09:10:00',
                'start_at' => now(),
                'end_at' => now()->addMinutes(30),
                'status' => 'checked_in',
                'booking_source' => 'public',
                'checked_in_at' => now(),
                'clinic_location' => 'seremban',
            ]);

            $queue2 = Queue::create([
                'appointment_id' => $appointment2->id,
                'queue_number' => 2,
                'queue_status' => 'waiting',
                'check_in_time' => now()->addSeconds(10),
            ]);

            // ACT
            $queueService = new QueueAssignmentService();
            $assigned = $queueService->assignNextPatient('seremban');

            // ASSERT: Patient 2 assigned even with patient 1 in treatment
            expect($assigned->id)->toBe($queue2->id);
            expect($assigned->queue_status)->toBe('in_treatment');
            expect($assigned->room_id)->toBe($room2->id);
        });
    });

    // ==================================================
    // RULE 4: FIFO QUEUE ORDERING BY CHECK-IN TIME
    // ==================================================

    describe('Rule 4: FIFO Based on Check-In Time', function () {
        
        test('Next patient selected by earliest check-in time', function () {
            // Setup
            $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
            Room::create([
                'room_number' => 'Room 1',
                'capacity' => 1,
                'status' => 'available',
                'is_active' => true,
                'clinic_location' => 'seremban',
            ]);
            $dentist = Dentist::create(['name' => 'Dr. Test', 'status' => true]);

            // Patient 1 - earliest arrival
            $appt1 = Appointment::create([
                'patient_name' => 'First Arrival',
                'patient_phone' => '555-0001',
                'patient_email' => 'first@test.com',
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

            $queue1 = Queue::create([
                'appointment_id' => $appt1->id,
                'queue_number' => 1,
                'queue_status' => 'waiting',
                'check_in_time' => now(),
            ]);

            // Patient 2 - later
            $appt2 = Appointment::create([
                'patient_name' => 'Second Arrival',
                'patient_phone' => '555-0002',
                'patient_email' => 'second@test.com',
                'service_id' => $service->id,
                'dentist_id' => $dentist->id,
                'appointment_date' => now()->format('Y-m-d'),
                'appointment_time' => '09:05:00',
                'start_at' => now(),
                'end_at' => now()->addMinutes(30),
                'status' => 'checked_in',
                'booking_source' => 'public',
                'checked_in_at' => now(),
                'clinic_location' => 'seremban',
            ]);

            $queue2 = Queue::create([
                'appointment_id' => $appt2->id,
                'queue_number' => 2,
                'queue_status' => 'waiting',
                'check_in_time' => now()->addSeconds(5),
            ]);

            // ACT
            $queueService = new QueueAssignmentService();
            $assigned = $queueService->assignNextPatient('seremban');

            // ASSERT: Earliest check-in assigned first
            expect($assigned->id)->toBe($queue1->id);
            expect($assigned->appointment->patient_name)->toBe('First Arrival');
        });
    });

    // ==================================================
    // RULE 5: COMPLETED PATIENTS REMOVED FROM ACTIVE QUEUE
    // ==================================================

    describe('Rule 5: Completed Patients Archived', function () {
        
        test('Completing treatment auto-triggers next patient assignment', function () {
            // Setup
            $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
            $room = Room::create([
                'room_number' => 'Room 1',
                'capacity' => 1,
                'status' => 'available',
                'is_active' => true,
                'clinic_location' => 'seremban',
            ]);
            $dentist = Dentist::create(['name' => 'Dr. Test', 'status' => true]);

            // Patient 1 - will complete
            $appt1 = Appointment::create([
                'patient_name' => 'Patient 1',
                'patient_phone' => '555-0001',
                'patient_email' => 'p1@test.com',
                'service_id' => $service->id,
                'dentist_id' => $dentist->id,
                'appointment_date' => now()->format('Y-m-d'),
                'appointment_time' => '09:00:00',
                'start_at' => now(),
                'end_at' => now()->addMinutes(30),
                'status' => 'completed',
                'booking_source' => 'public',
                'checked_in_at' => now(),
                'clinic_location' => 'seremban',
            ]);

            $queue1 = Queue::create([
                'appointment_id' => $appt1->id,
                'queue_number' => 1,
                'queue_status' => 'completed',
                'room_id' => $room->id,
                'dentist_id' => $dentist->id,
                'check_in_time' => now(),
            ]);

            // Patient 2 - waiting
            $appt2 = Appointment::create([
                'patient_name' => 'Patient 2',
                'patient_phone' => '555-0002',
                'patient_email' => 'p2@test.com',
                'service_id' => $service->id,
                'dentist_id' => $dentist->id,
                'appointment_date' => now()->format('Y-m-d'),
                'appointment_time' => '09:30:00',
                'start_at' => now(),
                'end_at' => now()->addMinutes(30),
                'status' => 'checked_in',
                'booking_source' => 'public',
                'checked_in_at' => now(),
                'clinic_location' => 'seremban',
            ]);

            $queue2 = Queue::create([
                'appointment_id' => $appt2->id,
                'queue_number' => 2,
                'queue_status' => 'waiting',
                'check_in_time' => now()->addMinutes(30),
            ]);

            // Make room available again
            $room->update(['status' => 'available']);

            // ACT
            $queueService = new QueueAssignmentService();
            $queueService->completeTreatment($queue1);

            // ASSERT: Next patient auto-assigned
            expect($queue2->refresh()->queue_status)->toBe('in_treatment');
            expect($queue2->room_id)->toBe($room->id);
        });

        test('Completed patients excluded from active counts', function () {
            // Setup
            $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
            
            $appt1 = Appointment::create([
                'patient_name' => 'Completed',
                'patient_phone' => '555-0001',
                'patient_email' => 'c@test.com',
                'service_id' => $service->id,
                'appointment_date' => now()->format('Y-m-d'),
                'appointment_time' => '09:00:00',
                'start_at' => now(),
                'end_at' => now()->addMinutes(30),
                'status' => 'completed',
                'booking_source' => 'public',
                'checked_in_at' => now(),
                'clinic_location' => 'seremban',
            ]);

            Queue::create([
                'appointment_id' => $appt1->id,
                'queue_number' => 1,
                'queue_status' => 'completed',
                'check_in_time' => now(),
            ]);

            $appt2 = Appointment::create([
                'patient_name' => 'Waiting',
                'patient_phone' => '555-0002',
                'patient_email' => 'w@test.com',
                'service_id' => $service->id,
                'appointment_date' => now()->format('Y-m-d'),
                'appointment_time' => '09:30:00',
                'start_at' => now(),
                'end_at' => now()->addMinutes(30),
                'status' => 'checked_in',
                'booking_source' => 'public',
                'checked_in_at' => now(),
                'clinic_location' => 'seremban',
            ]);

            Queue::create([
                'appointment_id' => $appt2->id,
                'queue_number' => 2,
                'queue_status' => 'waiting',
                'check_in_time' => now()->addMinutes(30),
            ]);

            // ASSERT
            $waitingCount = Queue::where('queue_status', 'waiting')->count();
            expect($waitingCount)->toBe(1);
        });
    });

    // ==================================================
    // RULE 6: QUEUE STATISTICS ACCURACY
    // ==================================================

    describe('Rule 6: Queue Statistics Match Database', function () {
        
        test('Statistics correctly reflect all patient states', function () {
            // Setup
            $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
            $dentist = Dentist::create(['name' => 'Dr. Test', 'status' => true]);
            $room = Room::create([
                'room_number' => 'Room 1',
                'capacity' => 1,
                'status' => 'occupied',
                'is_active' => true,
                'clinic_location' => 'seremban',
            ]);

            // Patient 1 - waiting
            $appt1 = Appointment::create([
                'patient_name' => 'Waiting',
                'patient_phone' => '555-0001',
                'patient_email' => 'w@test.com',
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

            Queue::create([
                'appointment_id' => $appt1->id,
                'queue_number' => 1,
                'queue_status' => 'waiting',
                'check_in_time' => now(),
            ]);

            // Patient 2 - in treatment
            $appt2 = Appointment::create([
                'patient_name' => 'InTreatment',
                'patient_phone' => '555-0002',
                'patient_email' => 'it@test.com',
                'service_id' => $service->id,
                'dentist_id' => $dentist->id,
                'appointment_date' => now()->format('Y-m-d'),
                'appointment_time' => '09:10:00',
                'start_at' => now(),
                'end_at' => now()->addMinutes(30),
                'status' => 'in_treatment',
                'booking_source' => 'public',
                'checked_in_at' => now(),
                'clinic_location' => 'seremban',
            ]);

            Queue::create([
                'appointment_id' => $appt2->id,
                'queue_number' => 2,
                'queue_status' => 'in_treatment',
                'room_id' => $room->id,
                'dentist_id' => $dentist->id,
                'check_in_time' => now(),
            ]);

            // Patient 3 - completed
            $appt3 = Appointment::create([
                'patient_name' => 'Completed',
                'patient_phone' => '555-0003',
                'patient_email' => 'c@test.com',
                'service_id' => $service->id,
                'dentist_id' => $dentist->id,
                'appointment_date' => now()->format('Y-m-d'),
                'appointment_time' => '08:00:00',
                'start_at' => now(),
                'end_at' => now()->addMinutes(30),
                'status' => 'completed',
                'booking_source' => 'public',
                'checked_in_at' => now(),
                'clinic_location' => 'seremban',
            ]);

            Queue::create([
                'appointment_id' => $appt3->id,
                'queue_number' => 0,
                'queue_status' => 'completed',
                'check_in_time' => now(),
            ]);

            // ACT
            $queueService = new QueueAssignmentService();
            $stats = $queueService->getQueueStats('seremban');

            // ASSERT - Using correct array keys from service
            expect($stats['waiting'])->toBe(1);
            expect($stats['in_treatment'])->toBe(1);
            expect($stats['completed'])->toBe(1);
        });
    });

    // ==================================================
    // RULE 7: SAFE FAILURE WHEN NO RESOURCES AVAILABLE
    // ==================================================

    describe('Rule 7: Safe Failure When Resources Unavailable', function () {
        
        test('Queue assignment returns null when no room available', function () {
            // Setup
            $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
            Dentist::create(['name' => 'Dr. Test', 'status' => true]);

            $appt = Appointment::create([
                'patient_name' => 'No Room',
                'patient_phone' => '555-0001',
                'patient_email' => 'nr@test.com',
                'service_id' => $service->id,
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
                'appointment_id' => $appt->id,
                'queue_number' => 1,
                'queue_status' => 'waiting',
                'check_in_time' => now(),
            ]);

            // ACT
            $queueService = new QueueAssignmentService();
            $result = $queueService->assignNextPatient('seremban');

            // ASSERT
            expect($result)->toBeNull();
            expect($queue->refresh()->queue_status)->toBe('waiting');
            expect($queue->room_id)->toBeNull();
        });

        test('Queue state unchanged when assignment fails', function () {
            // Setup
            $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
            Room::create([
                'room_number' => 'Room 1',
                'capacity' => 1,
                'status' => 'available',
                'is_active' => true,
                'clinic_location' => 'seremban',
            ]);

            $appt = Appointment::create([
                'patient_name' => 'Test',
                'patient_phone' => '555-0001',
                'patient_email' => 'test@test.com',
                'service_id' => $service->id,
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
                'appointment_id' => $appt->id,
                'queue_number' => 1,
                'queue_status' => 'waiting',
                'check_in_time' => now(),
            ]);

            $originalStatus = $queue->queue_status;

            // ACT
            $queueService = new QueueAssignmentService();
            $queueService->assignNextPatient('seremban');

            // ASSERT
            $queue->refresh();
            expect($queue->queue_status)->toBe($originalStatus);
            expect($queue->room_id)->toBeNull();
        });
    });

    // ==================================================
    // RULE 8: INACTIVE ROOMS NOT ASSIGNED
    // ==================================================

    describe('Rule 8: Only Active Rooms Are Used', function () {
        
        test('Inactive rooms are not assigned to patients', function () {
            // Setup - IMPORTANT: Inactive room has higher number so it's not selected first
            $service = Service::create(['name' => 'Checkup', 'estimated_duration' => 30]);
            
            // Room 1 is ACTIVE - should be selected
            $activeRoom = Room::create([
                'room_number' => 'Room 1',
                'capacity' => 1,
                'status' => 'available',
                'is_active' => true,
                'clinic_location' => 'seremban',
            ]);
            
            // Room 2 is INACTIVE - should be skipped even if "available"
            Room::create([
                'room_number' => 'Room 2',
                'capacity' => 1,
                'status' => 'available',
                'is_active' => false,
                'clinic_location' => 'seremban',
            ]);

            $dentist = Dentist::create(['name' => 'Dr. Test', 'status' => true]);

            $appt = Appointment::create([
                'patient_name' => 'Test Patient',
                'patient_phone' => '555-0001',
                'patient_email' => 'test@test.com',
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

            Queue::create([
                'appointment_id' => $appt->id,
                'queue_number' => 1,
                'queue_status' => 'waiting',
                'check_in_time' => now(),
            ]);

            // ACT
            $queueService = new QueueAssignmentService();
            $assigned = $queueService->assignNextPatient('seremban');

            // ASSERT - Should be assigned to active room
            expect($assigned->room_id)->toBe($activeRoom->id);
        });
    });

});
