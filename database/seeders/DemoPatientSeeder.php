<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoPatientSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Create demo patient accounts for system demonstration.
     * 
     * These accounts are safe to run multiple times - will only create if not already exists.
     * Credentials are simple for demo/testing purposes.
     */
    public function run()
    {
        // Demo Patient 1
        if (!User::where('email', 'patient@demo.local')->exists()) {
            User::create([
                'name' => 'Ahmad Rahman',
                'email' => 'patient@demo.local',
                'password' => Hash::make('password123'),
                'role' => 'patient',
                'phone' => '601155577037',
                'public_visible' => false,
            ]);
            $this->command->info('Patient account "Ahmad Rahman" (patient@demo.local) created successfully.');
        } else {
            $this->command->info('Patient account "Ahmad Rahman" already exists. Skipping.');
        }

        // Demo Patient 2
        if (!User::where('email', 'john@demo.local')->exists()) {
            User::create([
                'name' => 'John Tan',
                'email' => 'john@demo.local',
                'password' => Hash::make('password123'),
                'role' => 'patient',
                'phone' => '60138745213',
                'public_visible' => false,
            ]);
            $this->command->info('Patient account "John Tan" (john@demo.local) created successfully.');
        } else {
            $this->command->info('Patient account "John Tan" already exists. Skipping.');
        }

        // Demo Patient 3
        if (!User::where('email', 'sarah@demo.local')->exists()) {
            User::create([
                'name' => 'Sarah Wong',
                'email' => 'sarah@demo.local',
                'password' => Hash::make('password123'),
                'role' => 'patient',
                'phone' => '60165234891',
                'public_visible' => false,
            ]);
            $this->command->info('Patient account "Sarah Wong" (sarah@demo.local) created successfully.');
        } else {
            $this->command->info('Patient account "Sarah Wong" already exists. Skipping.');
        }
    }
}
