<?php

namespace Database\Seeders;

use App\Models\Dentist;
use App\Models\Room;
use App\Models\Service;
use Illuminate\Database\Seeder;

class QueueTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create dentists for testing
        $dentists = [
            ['name' => 'Dr. Smith', 'status' => true],
            ['name' => 'Dr. Johnson', 'status' => true],
            ['name' => 'Dr. Williams', 'status' => true],
            ['name' => 'Dr. Brown', 'status' => true],
            ['name' => 'Dr. Davis', 'status' => true],
            ['name' => 'Dr. Miller', 'status' => true],
        ];

        foreach ($dentists as $dentist) {
            Dentist::firstOrCreate(
                ['name' => $dentist['name']],
                ['status' => $dentist['status']]
            );
        }

        echo "✓ Created 6 dentists\n";

        // Create treatment rooms
        $rooms = [
            ['room_number' => 'A', 'is_active' => true],
            ['room_number' => 'B', 'is_active' => true],
            ['room_number' => 'C', 'is_active' => true],
        ];

        foreach ($rooms as $room) {
            Room::firstOrCreate(
                ['room_number' => $room['room_number']],
                ['is_active' => $room['is_active']]
            );
        }

        echo "✓ Created 3 treatment rooms\n";

        // Create services
        $services = [
            ['name' => 'General Checkup', 'duration' => 30],
            ['name' => 'Cleaning', 'duration' => 45],
            ['name' => 'Filling', 'duration' => 60],
            ['name' => 'Root Canal', 'duration' => 90],
            ['name' => 'Extraction', 'duration' => 45],
        ];

        foreach ($services as $service) {
            Service::firstOrCreate(
                ['name' => $service['name']],
                ['duration' => $service['duration']]
            );
        }

        echo "✓ Created 5 services\n";
        echo "\n=== Test Environment Ready ===\n";
        echo "Dentists: 6\n";
        echo "Treatment Rooms: 3\n";
        echo "Services: 5\n";
    }
}
