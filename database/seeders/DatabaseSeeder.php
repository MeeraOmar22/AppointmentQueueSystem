<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call([
            // Core data seeders (order matters)
            DentistSeeder::class,
            DentistScheduleSeeder::class,
            RoomSeeder::class,
            ServiceSeeder::class,
            AppointmentSeeder::class,
            
            // User account seeders
            DemoPatientSeeder::class,
            DeveloperUserSeeder::class,
            
            // Existing seeders
            InitialDataSeeder::class,
            TestDataSeeder::class,
        ]);
    }
}
