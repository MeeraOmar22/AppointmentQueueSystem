<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Dentist;
use App\Models\Room;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Idempotent: Only create if no appointments for today
        if (Appointment::whereDate('appointment_date', today())->exists()) {
            return;
        }

        $dentists = Dentist::all();
        $services = Service::all();

        if ($dentists->isEmpty() || $services->isEmpty()) {
            return; // Depend on other seeders
        }

        // Sample data for today
        $today = today();
        $times = ['09:00:00', '10:00:00', '11:00:00', '14:00:00', '15:00:00'];

        foreach ($times as $index => $time) {
            Appointment::create([
                'patient_name' => "Sample Patient " . ($index + 1),
                'patient_phone' => "012-345678" . ($index + 1),
                'patient_email' => "patient" . ($index + 1) . "@example.com",
                'dentist_id' => $dentists->random()->id,
                'service_id' => $services->random()->id,
                'appointment_date' => $today,
                'appointment_time' => $time,
                'status' => 'booked',
            ]);
        }
    }
}
