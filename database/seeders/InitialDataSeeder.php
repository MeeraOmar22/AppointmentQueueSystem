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
        // 1. Seed Services
        Service::insert([
            [
                'service_name' => 'Dental Checkup',
                'estimated_duration' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_name' => 'Scaling & Polishing',
                'estimated_duration' => 45,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_name' => 'Tooth Extraction',
                'estimated_duration' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 2. Seed Dentists
        Dentist::insert([
            [
                'dentist_name' => 'Dr. Aisyah',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'dentist_name' => 'Dr. Farhan',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 3. Seed Operating Hours (Mon–Fri)
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $hours = [];
        foreach ($days as $day) {
            $hours[] = [
                'day_of_week' => $day,
                'start_time' => '09:00:00',
                'end_time'   => '17:00:00',
            ];
        }
        OperatingHour::insert($hours);
    }
}
