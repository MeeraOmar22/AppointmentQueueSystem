<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogger;

class LateNoShowService
{
    /**
     * Check and mark late appointments
     * Runs periodically to mark patients as late if they haven't checked in
     * 
     * @param int $latenessThresholdMinutes Default 15 minutes after appointment time
     * @return int Number of appointments marked as late
     */
    public function markLateAppointments(int $latenessThresholdMinutes = 15): int
    {
        $marked = 0;

        // Get appointments that should have started but haven't been checked in
        $appointments = Appointment::whereDate('appointment_date', Carbon::today())
            ->where('status', 'booked')
            ->where(function ($query) {
                $query->whereNull('check_in_time');
            })
            ->get();

        foreach ($appointments as $appointment) {
            if ($this->isLate($appointment, $latenessThresholdMinutes)) {
                $appointment->markLate();
                $marked++;

                ActivityLogger::log(
                    'marked_late',
                    'Appointment',
                    $appointment->id,
                    'Appointment marked as late (no check-in after ' . $latenessThresholdMinutes . ' minutes)',
                    ['status' => 'booked'],
                    ['status' => 'late']
                );
            }
        }

        return $marked;
    }

    /**
     * Check and mark no-show appointments
     * Runs periodically to mark patients as no-show if they haven't checked in
     * 
     * @param int $noShowThresholdMinutes Default 30 minutes after appointment time
     * @return int Number of appointments marked as no-show
     */
    public function markNoShowAppointments(int $noShowThresholdMinutes = 30): int
    {
        $marked = 0;

        // Get booked/late appointments that are well past their time
        $appointments = Appointment::whereDate('appointment_date', Carbon::today())
            ->whereIn('status', ['booked', 'late'])
            ->where(function ($query) {
                $query->whereNull('check_in_time');
            })
            ->get();

        foreach ($appointments as $appointment) {
            if ($this->isNoShow($appointment, $noShowThresholdMinutes)) {
                $appointment->markNoShow();
                $marked++;

                // Also mark associated queue as completed
                if ($appointment->queue) {
                    $appointment->queue->update(['queue_status' => 'completed']);
                }

                ActivityLogger::log(
                    'marked_no_show',
                    'Appointment',
                    $appointment->id,
                    'Appointment marked as no-show',
                    ['status' => $appointment->status],
                    ['status' => 'no_show']
                );
            }
        }

        return $marked;
    }

    /**
     * Handle dentist becoming unavailable
     * Reassigns their patients to other dentists or pauses queue
     * 
     * @param int $dentistId
     * @param string $action 'reassign' or 'pause'
     * @return array
     */
    public function handleDentistUnavailable(int $dentistId, string $action = 'reassign'): array
    {
        $today = Carbon::today();
        $result = [
            'reassigned' => 0,
            'paused' => 0,
            'failed' => 0,
        ];

        return DB::transaction(function () use ($dentistId, $action, $today, $result) {
            // Get active queue entries for this dentist
            $queueEntries = Queue::where('dentist_id', $dentistId)
                ->where('queue_status', '!=', 'completed')
                ->whereHas('appointment', function ($query) use ($today) {
                    $query->whereDate('appointment_date', $today);
                })
                ->get();

            foreach ($queueEntries as $queue) {
                if ($action === 'reassign') {
                    // Try to reassign to another dentist
                    $newDentist = \App\Models\Dentist::where('id', '!=', $dentistId)
                        ->where('status', 'available')
                        ->first();

                    if ($newDentist) {
                        $queue->update(['dentist_id' => $newDentist->id]);
                        $result['reassigned']++;

                        ActivityLogger::log(
                            'dentist_reassigned',
                            'Appointment',
                            $queue->appointment->id,
                            'Reassigned to Dr. ' . $newDentist->name . ' (dentist unavailable)',
                            ['dentist_id' => $dentistId],
                            ['dentist_id' => $newDentist->id]
                        );
                    } else {
                        $result['failed']++;
                    }
                } else {
                    // Pause the queue entry
                    $queue->update(['queue_status' => 'paused']);
                    $result['paused']++;

                    ActivityLogger::log(
                        'queue_paused',
                        'Appointment',
                        $queue->appointment->id,
                        'Queue paused (dentist unavailable)',
                        ['queue_status' => $queue->queue_status],
                        ['queue_status' => 'paused']
                    );
                }
            }

            return $result;
        });
    }

    /**
     * Handle walk-in patient
     * Creates appointment and adds to queue immediately after existing arrivals
     * 
     * @param array $data
     * @return Appointment|null
     */
    public function createWalkIn(array $data): ?Appointment
    {
        return DB::transaction(function () use ($data) {
            // Create appointment for now
            $appointment = Appointment::create([
                'patient_name' => $data['patient_name'],
                'patient_phone' => $data['patient_phone'],
                'patient_email' => $data['patient_email'] ?? null,
                'service_id' => $data['service_id'],
                'dentist_id' => $data['dentist_id'] ?? null,
                'clinic_location' => $data['clinic_location'] ?? 'seremban',
                'appointment_date' => Carbon::today(),
                'appointment_time' => now()->format('H:i:s'),
                'status' => 'arrived',
                'check_in_time' => now(),
                'booking_source' => 'walk_in',
            ]);

            // Create queue entry
            $queue = Queue::create([
                'appointment_id' => $appointment->id,
                'queue_number' => Queue::nextNumberForDate(Carbon::today()),
                'queue_status' => 'waiting',
                'check_in_time' => now(),
            ]);

            ActivityLogger::log(
                'walk_in_created',
                'Appointment',
                $appointment->id,
                'Walk-in patient created: ' . $data['patient_name'],
                null,
                $appointment->toArray()
            );

            return $appointment;
        });
    }

    /**
     * Recover from page refresh/lost connection
     * Patient can access tracking via phone number + code
     * 
     * @param string $patientPhone
     * @param string $visitCode
     * @return Appointment|null
     */
    public function recoverAppointment(string $patientPhone, string $visitCode): ?Appointment
    {
        return Appointment::where('patient_phone', $patientPhone)
            ->where('visit_code', $visitCode)
            ->whereDate('appointment_date', Carbon::today())
            ->first();
    }

    /**
     * Check if appointment is late
     * 
     * @param Appointment $appointment
     * @param int $thresholdMinutes
     * @return bool
     */
    private function isLate(Appointment $appointment, int $thresholdMinutes): bool
    {
        $appointmentTime = $appointment->appointment_date->setTimeFromTimeString($appointment->appointment_time);
        $now = Carbon::now();

        return $now->diffInMinutes($appointmentTime) > $thresholdMinutes && $now > $appointmentTime;
    }

    /**
     * Check if appointment is no-show
     * 
     * @param Appointment $appointment
     * @param int $thresholdMinutes
     * @return bool
     */
    private function isNoShow(Appointment $appointment, int $thresholdMinutes): bool
    {
        $appointmentTime = $appointment->appointment_date->setTimeFromTimeString($appointment->appointment_time);
        $now = Carbon::now();

        return $now->diffInMinutes($appointmentTime) > $thresholdMinutes && $now > $appointmentTime;
    }
}
