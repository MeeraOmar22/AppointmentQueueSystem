<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Idempotent: Only create if not already exists
        if (Service::count() > 0) {
            return;
        }

        Service::create([
            'name' => 'General Checkup',
            'description' => 'Routine dental examination and cleaning',
            'price' => 100,
            'duration_minutes' => 30,
            'estimated_duration' => 30,
            'status' => 1,
        ]);

        Service::create([
            'name' => 'Teeth Cleaning',
            'description' => 'Professional teeth cleaning and scaling',
            'price' => 150,
            'duration_minutes' => 45,
            'estimated_duration' => 45,
            'status' => 1,
        ]);

        Service::create([
            'name' => 'Filling',
            'description' => 'Dental filling for cavities',
            'price' => 250,
            'duration_minutes' => 60,
            'estimated_duration' => 60,
            'status' => 1,
        ]);

        Service::create([
            'name' => 'Extraction',
            'description' => 'Tooth extraction procedure',
            'price' => 300,
            'duration_minutes' => 45,
            'estimated_duration' => 45,
            'status' => 1,
        ]);

        Service::create([
            'name' => 'Root Canal',
            'description' => 'Endodontic root canal treatment',
            'price' => 800,
            'duration_minutes' => 120,
            'estimated_duration' => 120,
            'status' => 1,
        ]);

        Service::create([
            'name' => 'Orthodontic Consultation',
            'description' => 'Initial orthodontic evaluation',
            'price' => 200,
            'duration_minutes' => 60,
            'estimated_duration' => 60,
            'status' => 1,
        ]);
    }
}
