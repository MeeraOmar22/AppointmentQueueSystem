<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DeveloperUserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Create a developer account for system diagnostics.
     * 
     * This seeder creates exactly ONE developer account.
     * It is safe to run multiple times - will only create if not already exists.
     * 
     * Developer account is NOT created through normal UI channels.
     * It is only accessible via database or this seeder.
     */
    public function run()
    {
        // Check if developer account already exists
        if (User::where('email', 'developer@system.local')->exists()) {
            $this->command->info('Developer account already exists. Skipping creation.');
            return;
        }

        // Create developer account
        User::create([
            'name' => 'Developer',
            'email' => 'developer@system.local',
            'password' => Hash::make('password123'),
            'role' => 'developer',
            'position' => 'System Administrator',
            'phone' => '0000000000',
            'public_visible' => false,
        ]);

        $this->command->info('Developer account created successfully.');
    }
}
