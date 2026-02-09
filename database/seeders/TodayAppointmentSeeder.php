<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Queue;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TodayAppointmentSeeder extends Seeder
{
    public function run(): void
    {
        // Clear all today's appointments and queues
        Appointment::whereDate('appointment_date', Carbon::today())->delete();
        Queue::whereDate('created_at', Carbon::today())->delete();

        $today = Carbon::today();
        $clinicLocation = config('clinic.location', 'seremban');

        // Create 5 test appointments for today
        $appointments = [
            [
                'patient_name' => 'John Doe',
                'appointment_date' => $today,
                'appointment_time' => '09:00:00',
                'status' => 'completed',
                'dentist_id' => 1,
                'room' => 1,
                'service_id' => 1,
                'checked_in_at' => $today->copy()->setTime(9, 0),
            ],
            [
                'patient_name' => 'Sarah Johnson',
                'appointment_date' => $today,
                'appointment_time' => '09:30:00',
                'status' => 'in_treatment',
                'dentist_id' => 2,
                'room' => 2,
                'service_id' => 2,
                'checked_in_at' => $today->copy()->setTime(9, 30),
            ],
            [
                'patient_name' => 'Michael Chen',
                'appointment_date' => $today,
                'appointment_time' => '10:00:00',
                'status' => 'waiting',
                'dentist_id' => 3,
                'room' => 3,
                'service_id' => 3,
                'checked_in_at' => $today->copy()->setTime(10, 0),
            ],
            [
                'patient_name' => 'Emma Wilson',
                'appointment_date' => $today,
                'appointment_time' => '10:30:00',
                'status' => 'waiting',
                'dentist_id' => 1,
                'room' => 1,
                'service_id' => 1,
                'checked_in_at' => $today->copy()->setTime(10, 30),
            ],
            [
                'patient_name' => 'David Brown',
                'appointment_date' => $today,
                'appointment_time' => '11:00:00',
                'status' => 'booked',
                'dentist_id' => 2,
                'room' => null,
                'service_id' => 2,
                'checked_in_at' => null,
            ],
            [
                'patient_name' => 'Lisa Anderson',
                'appointment_date' => $today,
                'appointment_time' => '14:00:00',
                'status' => 'booked',
                'dentist_id' => 3,
                'room' => null,
                'service_id' => 3,
                'checked_in_at' => null,
            ],
        ];

        foreach ($appointments as $index => $data) {
            $patient = User::where('email', 'testpatient' . ($index + 1) . '@test.com')->first();
            
            if (!$patient) {
                $patient = User::create([
                    'name' => $data['patient_name'],
                    'email' => 'testpatient' . ($index + 1) . '@test.com',
                    'password' => bcrypt('password'),
                    'phone' => '555-000' . ($index + 1),
                    'role' => 'staff',
                ]);
            }

            $visitCode = 'VIS' . $today->format('Ymd') . \Illuminate\Support\Str::random(6);

            $appointment = Appointment::create([
                'user_id' => $patient->id,
                'patient_name' => $data['patient_name'],
                'patient_phone' => $patient->phone,
                'service_id' => $data['service_id'],
                'dentist_id' => $data['dentist_id'],
                'room' => $data['room'],
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'status' => $data['status'],
                'clinic_location' => $clinicLocation,
                'visit_code' => $visitCode,
                'visit_token' => \Illuminate\Support\Str::uuid(),
                'checked_in_at' => $data['checked_in_at'],
            ]);

            // Create queue entry for checked-in appointments
            if (in_array($data['status'], ['completed', 'in_treatment', 'waiting'])) {
                Queue::create([
                    'appointment_id' => $appointment->id,
                    'queue_number' => Queue::whereDate('created_at', $today)->count() + 1,
                    'queue_status' => $data['status'],
                    'check_in_time' => $data['checked_in_at'],
                    'room_id' => $data['room'],
                    'dentist_id' => $data['dentist_id'],
                ]);
            }

            echo "✓ Created appointment: {$data['patient_name']} at {$data['appointment_time']} ({$data['status']}) - $visitCode\n";
        }

        echo "\n✅ Today's appointments seeded successfully!\n";
    }
}
