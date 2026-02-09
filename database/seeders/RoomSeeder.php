<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create rooms for Seremban clinic
        Room::create([
            'room_number' => 'Room 1',
            'capacity' => 1,
            'status' => 'available',
            'clinic_location' => 'seremban',
            'is_active' => true,
        ]);

        Room::create([
            'room_number' => 'Room 2',
            'capacity' => 1,
            'status' => 'available',
            'clinic_location' => 'seremban',
            'is_active' => true,
        ]);

        // Create rooms for Kuala Pilah clinic
        Room::create([
            'room_number' => 'Room 1',
            'capacity' => 1,
            'status' => 'available',
            'clinic_location' => 'kuala_pilah',
            'is_active' => true,
        ]);

        Room::create([
            'room_number' => 'Room 2',
            'capacity' => 1,
            'status' => 'available',
            'clinic_location' => 'kuala_pilah',
            'is_active' => true,
        ]);
    }
}
