<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\OperatingHour;
use Illuminate\Support\Facades\DB;

class OperatingHourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Single-clinic implementation: Creates operating hours for Seremban clinic only.
     * No redundant clinic_location filtering needed.
     */
    public function run(): void
    {
        // Delete existing operating hours
        OperatingHour::truncate();

        // Operating hours for single clinic (Seremban)
        // Using day names as strings to match blade template filtering
        // No session labels to avoid redundancy in display
        $operatingHours = [
            [
                'day_of_week' => 'Sunday',
                'clinic_location' => 'seremban',
                'session_label' => null,
                'start_time' => null,
                'end_time' => null,
                'is_closed' => true,
            ],
            [
                'day_of_week' => 'Monday',
                'clinic_location' => 'seremban',
                'session_label' => null,
                'start_time' => '09:00:00',
                'end_time' => '21:00:00',
                'is_closed' => false,
            ],
            [
                'day_of_week' => 'Tuesday',
                'clinic_location' => 'seremban',
                'session_label' => null,
                'start_time' => '09:00:00',
                'end_time' => '21:00:00',
                'is_closed' => false,
            ],
            [
                'day_of_week' => 'Wednesday',
                'clinic_location' => 'seremban',
                'session_label' => null,
                'start_time' => '09:00:00',
                'end_time' => '21:00:00',
                'is_closed' => false,
            ],
            [
                'day_of_week' => 'Thursday',
                'clinic_location' => 'seremban',
                'session_label' => null,
                'start_time' => '09:00:00',
                'end_time' => '21:00:00',
                'is_closed' => false,
            ],
            [
                'day_of_week' => 'Friday',
                'clinic_location' => 'seremban',
                'session_label' => null,
                'start_time' => '09:00:00',
                'end_time' => '21:00:00',
                'is_closed' => false,
            ],
            [
                'day_of_week' => 'Saturday',
                'clinic_location' => 'seremban',
                'session_label' => null,
                'start_time' => '09:00:00',
                'end_time' => '13:00:00',
                'is_closed' => false,
            ],
        ];

        foreach ($operatingHours as $hour) {
            OperatingHour::create($hour);
        }

        $this->command->info('Operating hours seeded successfully for single clinic!');
    }
}