<?php

namespace Database\Seeders;

use App\Models\Dentist;
use Illuminate\Database\Seeder;

class DentistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Idempotent: Only create if not already exists
        if (Dentist::count() > 0) {
            return;
        }

        // Create dentists with status as boolean (1=available, 0=unavailable)
        Dentist::create([
            'name' => 'Dr. Ahmad Dental',
            'specialization' => 'General Dentistry',
            'email' => 'ahmad@clinic.com',
            'phone' => '012-3456789',
            'years_of_experience' => 10,
            'status' => true, // Available
        ]);

        Dentist::create([
            'name' => 'Dr. Sarah Chen',
            'specialization' => 'Orthodontics',
            'email' => 'sarah@clinic.com',
            'phone' => '012-3456790',
            'years_of_experience' => 8,
            'status' => true, // Available
        ]);

        Dentist::create([
            'name' => 'Dr. Raj Kumar',
            'specialization' => 'Periodontics',
            'email' => 'raj@clinic.com',
            'phone' => '012-3456791',
            'years_of_experience' => 12,
            'status' => true, // Available
        ]);

        Dentist::create([
            'name' => 'Dr. Maria Garcia',
            'specialization' => 'Endodontics',
            'email' => 'maria@clinic.com',
            'phone' => '012-3456792',
            'years_of_experience' => 9,
            'status' => true, // Available
        ]);

        Dentist::create([
            'name' => 'Dr. Tan Wei Ming',
            'specialization' => 'Prosthodontics',
            'email' => 'tan@clinic.com',
            'phone' => '012-3456793',
            'years_of_experience' => 11,
            'status' => true, // Available
        ]);
    }
}
