<?php

use App\Models\Appointment;
use App\Models\Service;
use App\Models\Dentist;
use App\Models\Room;
use App\Models\Queue;
use Carbon\Carbon;

describe('Live Queue Board Testing', function () {

    beforeEach(function () {
        // Clear tables before each test
        Queue::truncate();
        Appointment::truncate();
    });

    // ========== TC-LQB-001: Empty Clinic Initialization ==========
    test('TC-LQB-001: Empty clinic initialization', function () {
        // Pre-condition: Fresh clinic start
        Queue::truncate();
        
        // Verify no queued patients
        expect(Queue::count())->toBe(0);
        
        // Verify rooms available
        $availableRooms = Room::where('is_active', true)->count();
        expect($availableRooms)->toBeGreaterThan(0);
        
        // Verify dentists available
        $availableDentists = Dentist::where('status', true)->count();
        expect($availableDentists)->toBeGreaterThan(0);
        
        // Verify counters
        $inTreatment = Queue::where('status', 'in_treatment')->count();
        $waiting = Queue::where('status', 'waiting')->count();
        expect($inTreatment)->toBe(0);
        expect($waiting)->toBe(0);
    })->group('queue');

    // ========== TC-LQB-002: Single Patient Check-in ==========
    test('TC-LQB-002: Single patient check-in', function () {
        // Pre-condition: One appointment booked
        $appointment = Appointment::create([
            'patient_name' => 'John Doe',
            'patient_phone' => '555-0001',
            'patient_email' => 'john@example.com',
            'service_id' => 1,
            'appointment_date' => Carbon::now()->format('Y-m-d'),
            'appointment_time' => '09:30:00',
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addMinutes(30),
            'status' => 'booked',
            'booking_source' => 'public',
        ]);
        $appointment->refresh();

        // Check-in
        $appointment->update(['status' => 'checked_in', 'checked_in_at' => now()]);
        
        // Create queue entry
        $queue = Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => 1,
            'status' => 'waiting',
            'checked_in_at' => now(),
        ]);

        // Verify in queue
        expect(Queue::where('appointment_id', $appointment->id)->count())->toBe(1);
        expect(Queue::where('status', 'waiting')->count())->toBe(1);
        expect($queue->queue_number)->toBe(1);
    })->group('queue');

    // ========== TC-LQB-003: Multiple Patient Check-ins ==========
    test('TC-LQB-003: Multiple patient check-ins (sequential)', function () {
        // Create 3 appointments
        $appointmentIds = [];
        for ($i = 1; $i <= 3; $i++) {
            $appt = Appointment::create([
                'patient_name' => "Patient $i",
                'patient_phone' => "555-000$i",
                'patient_email' => "patient$i@example.com",
                'service_id' => 1,
                'appointment_date' => Carbon::now()->format('Y-m-d'),
                'appointment_time' => sprintf('%02d:30:00', 8 + $i),
                'start_at' => Carbon::now()->addMinutes($i * 10),
                'end_at' => Carbon::now()->addMinutes($i * 10 + 30),
                'status' => 'booked',
                'booking_source' => 'public',
            ]);
            $appt->refresh();
            $appointmentIds[] = $appt->id;
        }

        // Check in sequentially (10 minute intervals)
        foreach ($appointmentIds as $index => $id) {
            $appt = Appointment::find($id);
            $appt->update(['status' => 'checked_in', 'checked_in_at' => now()->addMinutes($index * 10)]);
            
            Queue::create([
                'appointment_id' => $id,
                'queue_number' => $index + 1,
                'status' => 'waiting',
                'checked_in_at' => now()->addMinutes($index * 10),
            ]);
        }

        // Verify queue order
        $queue = Queue::where('status', 'waiting')
            ->orderBy('queue_number')
            ->pluck('queue_number')
            ->toArray();
        
        expect($queue)->toBe([1, 2, 3]);
        expect(Queue::where('status', 'waiting')->count())->toBe(3);
    })->group('queue');

    // ========== TC-LQB-004: Queue Ordering by Check-in Time ==========
    test('TC-LQB-004: Queue ordering by check-in time (not appointment time)', function () {
        // Create appointments at different times but check in out of order
        $appointmentA = Appointment::create([
            'patient_name' => 'Patient A',
            'patient_phone' => '555-0001',
            'appointment_date' => Carbon::now()->format('Y-m-d'),
            'appointment_time' => '09:30:00',
            'start_at' => Carbon::now(),
            'end_at' => Carbon::now()->addMinutes(30),
            'status' => 'booked',
        ]);
        $appointmentA->refresh();

        $appointmentC = Appointment::create([
            'patient_name' => 'Patient C',
            'patient_phone' => '555-0003',
            'appointment_date' => Carbon::now()->format('Y-m-d'),
            'appointment_time' => '09:35:00',
            'start_at' => Carbon::now()->addMinutes(5),
            'end_at' => Carbon::now()->addMinutes(35),
            'status' => 'booked',
        ]);
        $appointmentC->refresh();

        $appointmentB = Appointment::create([
            'patient_name' => 'Patient B',
            'patient_phone' => '555-0002',
            'appointment_date' => Carbon::now()->format('Y-m-d'),
            'appointment_time' => '09:45:00',
            'start_at' => Carbon::now()->addMinutes(15),
            'end_at' => Carbon::now()->addMinutes(45),
            'status' => 'booked',
        ]);
        $appointmentB->refresh();

        // Check in: A (first), C (second), B (third)
        $checkInA = now();
        $checkInC = now()->addMinutes(5);
        $checkInB = now()->addMinutes(15);

        Queue::create(['appointment_id' => $appointmentA->id, 'queue_number' => 1, 'status' => 'waiting', 'checked_in_at' => $checkInA]);
        Queue::create(['appointment_id' => $appointmentC->id, 'queue_number' => 2, 'status' => 'waiting', 'checked_in_at' => $checkInC]);
        Queue::create(['appointment_id' => $appointmentB->id, 'queue_number' => 3, 'status' => 'waiting', 'checked_in_at' => $checkInB]);

        // Verify order is by check-in time, not appointment time
        $queue = Queue::orderBy('checked_in_at')->pluck('appointment_id')->toArray();
        expect($queue)->toBe([$appointmentA->id, $appointmentC->id, $appointmentB->id]);
    })->group('queue');

    // ========== TC-LQB-005: Call Next Patient with Available Room ==========
    test('TC-LQB-005: Call next patient with available room', function () {
        // Setup: 3 in queue, 1 room available
        $appointments = [];
        for ($i = 1; $i <= 3; $i++) {
            $appt = Appointment::create([
                'patient_name' => "Patient $i",
                'patient_phone' => "555-000$i",
                'appointment_date' => Carbon::now()->format('Y-m-d'),
                'appointment_time' => sprintf('%02d:30:00', 8 + $i),
                'start_at' => now()->addMinutes($i * 10),
                'end_at' => now()->addMinutes($i * 10 + 30),
                'status' => 'booked',
            ]);
            $appt->refresh();
            $appointments[] = $appt;

            Queue::create([
                'appointment_id' => $appt->id,
                'queue_number' => $i,
                'status' => 'waiting',
                'checked_in_at' => now()->addMinutes($i * 10),
            ]);
        }

        // Call first patient to Room 1
        $firstPatient = $appointments[0];
        $queue = Queue::where('appointment_id', $firstPatient->id)->first();
        $queue->update(['status' => 'in_treatment', 'called_at' => now()]);
        $firstPatient->update(['status' => 'in_treatment']);

        // Verify state
        expect(Queue::where('status', 'in_treatment')->count())->toBe(1);
        expect(Queue::where('status', 'waiting')->count())->toBe(2);
        expect($queue->status)->toBe('in_treatment');
    })->group('queue');

    // ========== TC-LQB-006: Parallel Treatment with Multiple Rooms ==========
    test('TC-LQB-006: Parallel treatment with multiple rooms', function () {
        // Setup: 5 in queue, 3 rooms available
        $appointments = [];
        for ($i = 1; $i <= 5; $i++) {
            $appt = Appointment::create([
                'patient_name' => "Patient $i",
                'patient_phone' => "555-000$i",
                'appointment_date' => Carbon::now()->format('Y-m-d'),
                'appointment_time' => sprintf('%02d:30:00', 8 + $i),
                'start_at' => now()->addMinutes($i * 10),
                'end_at' => now()->addMinutes($i * 10 + 30),
                'status' => 'booked',
            ]);
            $appt->refresh();
            $appointments[] = $appt;

            Queue::create([
                'appointment_id' => $appt->id,
                'queue_number' => $i,
                'status' => 'waiting',
                'checked_in_at' => now()->addMinutes($i * 10),
            ]);
        }

        // Call first 3 patients to treatment
        for ($i = 0; $i < 3; $i++) {
            $queue = Queue::where('appointment_id', $appointments[$i]->id)->first();
            $queue->update(['status' => 'in_treatment', 'room_id' => $i + 1, 'called_at' => now()]);
            $appointments[$i]->update(['status' => 'in_treatment']);
        }

        // Verify parallel treatment
        expect(Queue::where('status', 'in_treatment')->count())->toBe(3);
        expect(Queue::where('status', 'waiting')->count())->toBe(2);
    })->group('queue');

    // ========== TC-LQB-007: Prevention of FIFO Blocking ==========
    test('TC-LQB-007: Prevention of FIFO blocking', function () {
        // Setup: 4 in queue, Patient 1 in treatment
        $appointments = [];
        for ($i = 1; $i <= 4; $i++) {
            $appt = Appointment::create([
                'patient_name' => "Patient $i",
                'patient_phone' => "555-000$i",
                'appointment_date' => Carbon::now()->format('Y-m-d'),
                'appointment_time' => sprintf('%02d:30:00', 8 + $i),
                'start_at' => now()->addMinutes($i * 10),
                'end_at' => now()->addMinutes($i * 10 + 30),
                'status' => 'booked',
            ]);
            $appt->refresh();
            $appointments[] = $appt;

            Queue::create([
                'appointment_id' => $appt->id,
                'queue_number' => $i,
                'status' => 'waiting',
                'checked_in_at' => now()->addMinutes($i * 10),
            ]);
        }

        // Put Patient 1 in treatment
        $queue1 = Queue::where('appointment_id', $appointments[0]->id)->first();
        $queue1->update(['status' => 'in_treatment', 'room_id' => 1, 'called_at' => now()]);

        // Should be able to call Patient 2 without waiting for Patient 1 completion
        $queue2 = Queue::where('appointment_id', $appointments[1]->id)->first();
        $queue2->update(['status' => 'in_treatment', 'room_id' => 2, 'called_at' => now()]);

        // Verify both in treatment
        expect(Queue::where('status', 'in_treatment')->count())->toBe(2);
        expect(Queue::where('status', 'waiting')->count())->toBe(2);
        
        // Verify no FIFO blocking
        $inTreatment = Queue::where('status', 'in_treatment')->orderBy('queue_number')->pluck('queue_number')->toArray();
        expect($inTreatment)->toBe([1, 2]);
    })->group('queue');

    // ========== TC-LQB-008: No Rooms Available ==========
    test('TC-LQB-008: Handling when no rooms available', function () {
        // Setup: 5 in queue, all 3 rooms occupied
        $appointments = [];
        for ($i = 1; $i <= 5; $i++) {
            $appt = Appointment::create([
                'patient_name' => "Patient $i",
                'patient_phone' => "555-000$i",
                'appointment_date' => Carbon::now()->format('Y-m-d'),
                'appointment_time' => sprintf('%02d:30:00', 8 + $i),
                'start_at' => now()->addMinutes($i * 10),
                'end_at' => now()->addMinutes($i * 10 + 30),
                'status' => 'booked',
            ]);
            $appt->refresh();
            $appointments[] = $appt;

            Queue::create([
                'appointment_id' => $appt->id,
                'queue_number' => $i,
                'status' => 'waiting',
                'checked_in_at' => now()->addMinutes($i * 10),
            ]);
        }

        // Occupy all 3 rooms
        for ($i = 0; $i < 3; $i++) {
            $queue = Queue::where('appointment_id', $appointments[$i]->id)->first();
            $queue->update(['status' => 'in_treatment', 'room_id' => $i + 1, 'called_at' => now()]);
        }

        // Verify no rooms available for next patient
        $availableRooms = Room::where('is_active', true)->count();
        $inTreatmentCount = Queue::where('status', 'in_treatment')->count();
        
        expect($inTreatmentCount)->toBe(3);
        expect(Queue::where('status', 'waiting')->count())->toBe(2);
    })->group('queue');

    // ========== TC-LQB-009: Treatment Completion and Room Release ==========
    test('TC-LQB-009: Treatment completion and room release', function () {
        // Setup: 3 in treatment
        $appointments = [];
        for ($i = 1; $i <= 5; $i++) {
            $appt = Appointment::create([
                'patient_name' => "Patient $i",
                'patient_phone' => "555-000$i",
                'appointment_date' => Carbon::now()->format('Y-m-d'),
                'appointment_time' => sprintf('%02d:30:00', 8 + $i),
                'start_at' => now()->addMinutes($i * 10),
                'end_at' => now()->addMinutes($i * 10 + 30),
                'status' => 'booked',
            ]);
            $appt->refresh();
            $appointments[] = $appt;

            Queue::create([
                'appointment_id' => $appt->id,
                'queue_number' => $i,
                'status' => $i <= 3 ? 'in_treatment' : 'waiting',
                'checked_in_at' => now()->addMinutes($i * 10),
                'room_id' => $i <= 3 ? $i : null,
                'called_at' => $i <= 3 ? now() : null,
            ]);

            if ($i <= 3) {
                $appt->update(['status' => 'in_treatment']);
            }
        }

        // Complete first treatment
        $queue1 = Queue::where('appointment_id', $appointments[0]->id)->first();
        $queue1->update(['status' => 'completed', 'completed_at' => now()]);
        $appointments[0]->update(['status' => 'completed']);

        // Verify state
        expect(Queue::where('status', 'completed')->count())->toBe(1);
        expect(Queue::where('status', 'in_treatment')->count())->toBe(2);
        expect(Queue::where('status', 'waiting')->count())->toBe(2);
    })->group('queue');

    // ========== TC-LQB-010: Queue Auto-update After Room Available ==========
    test('TC-LQB-010: Queue auto-update after room becomes available', function () {
        // Setup: Patient in each room, waiting patients
        $appointments = [];
        for ($i = 1; $i <= 5; $i++) {
            $appt = Appointment::create([
                'patient_name' => "Patient $i",
                'patient_phone' => "555-000$i",
                'appointment_date' => Carbon::now()->format('Y-m-d'),
                'appointment_time' => sprintf('%02d:30:00', 8 + $i),
                'start_at' => now()->addMinutes($i * 10),
                'end_at' => now()->addMinutes($i * 10 + 30),
                'status' => 'booked',
            ]);
            $appt->refresh();
            $appointments[] = $appt;
        }

        // Queue assignments
        for ($i = 1; $i <= 5; $i++) {
            Queue::create([
                'appointment_id' => $appointments[$i-1]->id,
                'queue_number' => $i,
                'status' => $i <= 3 ? 'in_treatment' : 'waiting',
                'checked_in_at' => now()->addMinutes($i * 10),
                'room_id' => $i <= 3 ? $i : null,
                'called_at' => $i <= 3 ? now() : null,
            ]);

            if ($i <= 3) {
                $appointments[$i-1]->update(['status' => 'in_treatment']);
            }
        }

        // Patient 1 completes, freeing Room 1
        $queue1 = Queue::where('appointment_id', $appointments[0]->id)->first();
        $queue1->update(['status' => 'completed', 'completed_at' => now()]);
        $appointments[0]->update(['status' => 'completed']);

        // Verify next patient (Patient 4) is ready to be called
        $nextWaiting = Queue::where('status', 'waiting')
            ->orderBy('queue_number')
            ->first();
        
        expect($nextWaiting->queue_number)->toBe(4);
        expect($nextWaiting->appointment_id)->toBe($appointments[3]->id);
    })->group('queue');

    // ========== TC-LQB-011: Dentist Availability Status ==========
    test('TC-LQB-011: Dentist availability status update', function () {
        // Get test dentists
        $dentist1 = Dentist::firstOrCreate(
            ['name' => 'Dr. Smith'],
            ['status' => true]
        );
        $dentist2 = Dentist::firstOrCreate(
            ['name' => 'Dr. Johnson'],
            ['status' => true]
        );

        // Create appointments
        $appt1 = Appointment::create([
            'patient_name' => 'Patient 1',
            'patient_phone' => '555-0001',
            'appointment_date' => Carbon::now()->format('Y-m-d'),
            'appointment_time' => '09:30:00',
            'start_at' => now(),
            'end_at' => now()->addMinutes(30),
            'dentist_id' => $dentist1->id,
            'status' => 'in_treatment',
        ]);
        $appt1->refresh();

        $appt2 = Appointment::create([
            'patient_name' => 'Patient 2',
            'patient_phone' => '555-0002',
            'appointment_date' => Carbon::now()->format('Y-m-d'),
            'appointment_time' => '09:35:00',
            'start_at' => now()->addMinutes(5),
            'end_at' => now()->addMinutes(35),
            'dentist_id' => $dentist2->id,
            'status' => 'in_treatment',
        ]);
        $appt2->refresh();

        // Mark dentists as unavailable when in treatment
        $dentist1->update(['status' => false]);
        $dentist2->update(['status' => false]);

        // Verify status
        expect($dentist1->fresh()->status)->toBe(false);
        expect($dentist2->fresh()->status)->toBe(false);

        // Complete treatment for dentist1
        $appt1->update(['status' => 'completed']);
        $dentist1->update(['status' => true]);

        // Verify dentist1 available, dentist2 still unavailable
        expect($dentist1->fresh()->status)->toBe(true);
        expect($dentist2->fresh()->status)->toBe(false);
    })->group('queue');

    // ========== TC-LQB-012: Completed-Today Counter Accuracy ==========
    test('TC-LQB-012: Completed-today counter accuracy', function () {
        // Create and complete 5 appointments
        for ($i = 1; $i <= 5; $i++) {
            $appt = Appointment::create([
                'patient_name' => "Patient $i",
                'patient_phone' => "555-000$i",
                'appointment_date' => Carbon::now()->format('Y-m-d'),
                'appointment_time' => sprintf('%02d:30:00', 8 + $i),
                'start_at' => now()->addMinutes($i * 10),
                'end_at' => now()->addMinutes($i * 10 + 30),
                'status' => 'completed',
            ]);
            $appt->refresh();
        }

        // Verify counter
        $completedCount = Appointment::where('status', 'completed')
            ->whereDate('created_at', Carbon::now())
            ->count();
        
        expect($completedCount)->toBe(5);
    })->group('queue');

    // ========== TC-LQB-013: Real-time Dashboard Refresh ==========
    test('TC-LQB-013: Real-time dashboard refresh consistency', function () {
        // Create 5 patients
        $appointments = [];
        for ($i = 1; $i <= 5; $i++) {
            $appt = Appointment::create([
                'patient_name' => "Patient $i",
                'patient_phone' => "555-000$i",
                'appointment_date' => Carbon::now()->format('Y-m-d'),
                'appointment_time' => sprintf('%02d:30:00', 8 + $i),
                'start_at' => now()->addMinutes($i * 10),
                'end_at' => now()->addMinutes($i * 10 + 30),
                'status' => 'booked',
            ]);
            $appt->refresh();
            $appointments[] = $appt;

            Queue::create([
                'appointment_id' => $appt->id,
                'queue_number' => $i,
                'status' => $i <= 2 ? 'in_treatment' : 'waiting',
                'checked_in_at' => now()->addMinutes($i * 10),
                'room_id' => $i <= 2 ? $i : null,
                'called_at' => $i <= 2 ? now() : null,
            ]);

            if ($i <= 2) {
                $appt->update(['status' => 'in_treatment']);
            }
        }

        // Get dashboard state (simulating refresh)
        $inTreatment = Queue::where('status', 'in_treatment')->count();
        $waiting = Queue::where('status', 'waiting')->count();
        $completed = Appointment::where('status', 'completed')
            ->whereDate('created_at', Carbon::now())
            ->count();

        // Verify consistency
        expect($inTreatment)->toBe(2);
        expect($waiting)->toBe(3);
        expect($completed)->toBe(0);

        // Simulate another refresh (should be same)
        $inTreatment2 = Queue::where('status', 'in_treatment')->count();
        $waiting2 = Queue::where('status', 'waiting')->count();

        expect($inTreatment2)->toBe($inTreatment);
        expect($waiting2)->toBe($waiting);
    })->group('queue');

    // ========== TC-LQB-015: Edge Case - Empty Queue ==========
    test('TC-LQB-015: Edge case - calling next with empty queue', function () {
        // Verify queue is empty
        expect(Queue::count())->toBe(0);
        expect(Queue::where('status', 'waiting')->count())->toBe(0);

        // Attempt to get next patient
        $nextPatient = Queue::where('status', 'waiting')
            ->orderBy('queue_number')
            ->first();

        // Should be null
        expect($nextPatient)->toBeNull();
    })->group('queue');

    // ========== TC-LQB-016: Edge Case - Complete Non-existent Treatment ==========
    test('TC-LQB-016: Edge case - ending treatment without active patient', function () {
        // Create empty room scenario
        $emptyRoomId = 1;

        // Try to find patient in room
        $patientInRoom = Queue::where('room_id', $emptyRoomId)
            ->where('status', 'in_treatment')
            ->first();

        // Should be null
        expect($patientInRoom)->toBeNull();
    })->group('queue');

});
