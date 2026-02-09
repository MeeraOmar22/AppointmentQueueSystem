<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\Dentist;
use App\Models\OperatingHour;

class InitialDataSeeder extends Seeder
{
    public function run()
    {
        // Idempotent: Skip if data already exists
        if (OperatingHour::count() > 0) {
            return;
        }

        // 1. Seed Services (using ServiceSeeder now)
        // Kept for backward compatibility but mostly handled by ServiceSeeder
        if (Service::count() === 0) {
            Service::create([
                'name' => 'Dental Checkup',
                'estimated_duration' => 30,
                'duration_minutes' => 30,
                'price' => 50,
                'description' => 'Routine dental checkup',
                'status' => 1,
            ]);
        }

        // 2. Seed Dentists (using DentistSeeder now)
        // Kept for backward compatibility but mostly handled by DentistSeeder
        if (Dentist::count() === 0) {
            Dentist::create([
                'name' => 'Dr. Aisyah',
                'specialization' => 'General Dentistry',
                'status' => true,
            ]);
        }

        // 3. Seed Operating Hours (Mon–Fri)
        // Using proper mutator via create() instead of insert()
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        foreach ($days as $day) {
            // Check if already exists (idempotent)
            if (!OperatingHour::where('day_of_week', $day)->exists()) {
                OperatingHour::create([
                    'day_of_week' => $day,
                    'start_time' => '09:00:00',
                    'end_time' => '23:00:00', // Updated to 11 PM
                ]);
            }
        }
    }
}

