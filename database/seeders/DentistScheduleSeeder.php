<?php

namespace Database\Seeders;

use App\Models\Dentist;
use App\Models\DentistSchedule;
use Illuminate\Database\Seeder;

class DentistScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Idempotent: Only create if schedules don't exist
        if (DentistSchedule::count() > 0) {
            return;
        }

        $dentists = Dentist::all();
        if ($dentists->isEmpty()) {
            return; // Depend on DentistSeeder
        }

        // Days of week in canonical format
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        // Clinic hours: 9 AM to 11 PM (9:00:00 to 23:00:00)
        $startTime = '09:00:00';
        $endTime = '23:00:00';

        // Create schedule for each dentist, each day
        foreach ($dentists as $dentist) {
            foreach ($days as $day) {
                // All days available (you can customize per dentist if needed)
                DentistSchedule::create([
                    'dentist_id' => $dentist->id,
                    'day_of_week' => $day, // Mutator normalizes this automatically
                    'is_available' => true,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ]);
            }
        }
    }
}
