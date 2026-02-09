<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;

class TestAppointmentSeeder extends Seeder
{
    public function run(): void
    {
        // Delete previous test appointment if exists
        Appointment::where('patient_name', 'Test Booked Patient')->delete();

        // Create a new test appointment with booked status
        Appointment::create([
            'patient_name' => 'Test Booked Patient',
            'patient_phone' => '0123456789',
            'patient_email' => 'test@example.com',
            'appointment_date' => today(),
            'appointment_time' => now(),
            'status' => 'booked',
            'dentist_id' => 1,
            'service_id' => 1, // General Checkup
            'visit_token' => \Illuminate\Support\Str::uuid(),
            'visit_code' => 'DNT-' . now()->format('Ymd') . '-999',
        ]);

        echo "✓ Test appointment created with status: booked\n";
    }
}
