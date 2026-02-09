<?php

namespace Tests\Helpers;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Queue;
use Carbon\Carbon;

class QueueTestHelper
{
    /**
     * Create a test appointment
     */
    public static function createAppointment(array $overrides = []): Appointment
    {
        $data = array_merge([
            'patient_name' => fake()->name(),
            'patient_phone' => fake()->phoneNumber(),
            'patient_email' => fake()->email(),
            'service_id' => 1,
            'appointment_date' => Carbon::now()->format('Y-m-d'),
            'appointment_time' => '09:30:00',
            'start_at' => now(),
            'end_at' => now()->addMinutes(30),
            'status' => 'booked',
            'booking_source' => 'public',
        ], $overrides);

        $appointment = Appointment::create($data);
        return $appointment->refresh();
    }

    /**
     * Create multiple test appointments
     */
    public static function createAppointments(int $count = 5, array $overrides = []): array
    {
        $appointments = [];
        for ($i = 1; $i <= $count; $i++) {
            $appointments[] = self::createAppointment(array_merge($overrides, [
                'patient_name' => "Test Patient $i",
                'patient_phone' => "555-000$i",
                'patient_email' => "patient$i@test.com",
                'appointment_time' => sprintf('%02d:%02d:00', 9, 30 + ($i - 1) * 5),
                'start_at' => now()->addMinutes(($i - 1) * 5),
                'end_at' => now()->addMinutes(($i - 1) * 5 + 30),
            ]));
        }
        return $appointments;
    }

    /**
     * Check in an appointment
     */
    public static function checkInAppointment(Appointment $appointment): Queue
    {
        $appointment->update(['status' => 'checked_in', 'checked_in_at' => now()]);

        return Queue::create([
            'appointment_id' => $appointment->id,
            'queue_number' => Queue::max('queue_number') + 1,
            'status' => 'waiting',
            'checked_in_at' => now(),
        ]);
    }

    /**
     * Call next patient to treatment
     */
    public static function callNextPatient(int $roomId = 1, int $dentistId = 1): ?Queue
    {
        $nextPatient = Queue::where('status', 'waiting')
            ->orderBy('queue_number')
            ->first();

        if (!$nextPatient) {
            return null;
        }

        $nextPatient->update([
            'status' => 'in_treatment',
            'room_id' => $roomId,
            'dentist_id' => $dentistId,
            'called_at' => now(),
        ]);

        $nextPatient->appointment->update(['status' => 'in_treatment']);

        return $nextPatient->refresh();
    }

    /**
     * Complete treatment for a patient
     */
    public static function completePatient(Queue $queue): Queue
    {
        $queue->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $queue->appointment->update(['status' => 'completed']);

        return $queue->refresh();
    }

    /**
     * Get queue dashboard stats
     */
    public static function getDashboardStats(): array
    {
        return [
            'in_treatment' => Queue::where('status', 'in_treatment')->count(),
            'waiting' => Queue::where('status', 'waiting')->count(),
            'completed' => Appointment::where('status', 'completed')
                ->whereDate('created_at', Carbon::now())
                ->count(),
            'available_rooms' => \App\Models\Room::where('is_available', true)->count(),
            'available_dentists' => Dentist::where('is_available', true)->count(),
        ];
    }

    /**
     * Verify queue FIFO ordering
     */
    public static function verifyQueueOrder(): bool
    {
        $queue = Queue::where('status', 'waiting')
            ->orderBy('queue_number')
            ->get();

        $queueNumbers = $queue->pluck('queue_number')->toArray();
        
        return $queueNumbers === array_values(array_unique($queueNumbers)) && 
               $queueNumbers === range(min($queueNumbers) ?? 1, max($queueNumbers) ?? 1);
    }

    /**
     * Simulate clinic morning session
     */
    public static function simulateMorningSession(int $appointmentCount = 8): array
    {
        $results = [
            'appointments' => [],
            'events' => [],
        ];

        // Create appointments
        $appointments = self::createAppointments($appointmentCount);
        $results['appointments'] = $appointments;

        // Check in first 3 patients (simulated check-in times)
        for ($i = 0; $i < min(3, count($appointments)); $i++) {
            $queue = self::checkInAppointment($appointments[$i]);
            $results['events'][] = [
                'time' => now(),
                'action' => 'checked_in',
                'patient' => $appointments[$i]->patient_name,
                'queue_number' => $queue->queue_number,
            ];
        }

        // Call first 2 to treatment
        for ($i = 0; $i < 2; $i++) {
            $queue = self::callNextPatient($i + 1, $i + 1);
            if ($queue) {
                $results['events'][] = [
                    'time' => now(),
                    'action' => 'called_to_treatment',
                    'patient' => $queue->appointment->patient_name,
                    'room' => $i + 1,
                ];
            }
        }

        return $results;
    }

    /**
     * Create concurrent operation scenario
     */
    public static function simulateConcurrentOperations(): array
    {
        $results = [
            'appointments' => [],
            'operations' => [],
        ];

        // Create appointments
        $appointments = self::createAppointments(5);
        $results['appointments'] = $appointments;

        // Check in all 5
        foreach ($appointments as $appt) {
            self::checkInAppointment($appt);
        }

        // Simulate concurrent calls (rapid assignment)
        $results['operations'][] = [
            'type' => 'concurrent_call',
            'patient_1' => self::callNextPatient(1, 1)?->appointment->patient_name,
            'patient_2' => self::callNextPatient(2, 2)?->appointment->patient_name,
        ];

        // Verify no conflicts
        $inTreatment = Queue::where('status', 'in_treatment')->get();
        $results['operations'][] = [
            'type' => 'verification',
            'in_treatment_count' => $inTreatment->count(),
            'unique_patients' => $inTreatment->pluck('appointment_id')->unique()->count(),
            'no_conflicts' => $inTreatment->count() === $inTreatment->pluck('appointment_id')->unique()->count(),
        ];

        return $results;
    }
}
