<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Service;
use App\Models\Dentist;
use App\Models\Appointment;
use App\Models\Queue;
use App\Models\Feedback;
use App\Models\ActivityLog;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "\n🔄 Seeding test data...\n\n";

        // 1. Create Staff Users
        echo "📝 Creating staff users...\n";
        $admin = User::firstOrCreate(
            ['email' => 'admin@clinic.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password123'),
                'role' => 'staff',
            ]
        );

        $staff1 = User::firstOrCreate(
            ['email' => 'staff1@clinic.com'],
            [
                'name' => 'Sarah Johnson',
                'password' => bcrypt('password123'),
                'role' => 'staff',
            ]
        );

        $staff2 = User::firstOrCreate(
            ['email' => 'staff2@clinic.com'],
            [
                'name' => 'Michael Lee',
                'password' => bcrypt('password123'),
                'role' => 'staff',
            ]
        );

        echo "✅ Created 3 staff users\n";

        // 2. Create Treatment Rooms
        echo "📝 Creating treatment rooms...\n";
        $rooms = [
            ['room_number' => 'Room 1', 'clinic_location' => 'seremban', 'capacity' => 1, 'status' => 1],
            ['room_number' => 'Room 2', 'clinic_location' => 'seremban', 'capacity' => 1, 'status' => 1],
            ['room_number' => 'Room 3', 'clinic_location' => 'seremban', 'capacity' => 1, 'status' => 1],
            ['room_number' => 'Room 1', 'clinic_location' => 'kuala_pilah', 'capacity' => 1, 'status' => 1],
            ['room_number' => 'Room 2', 'clinic_location' => 'kuala_pilah', 'capacity' => 1, 'status' => 1],
        ];

        foreach ($rooms as $room) {
            Room::firstOrCreate(
                ['room_number' => $room['room_number'], 'clinic_location' => $room['clinic_location']],
                $room
            );
        }
        echo "✅ Created treatment rooms\n";

        // 3. Create Additional Services (the InitialDataSeeder creates the first 3)
        echo "📝 Creating additional services...\n";
        $additionalServices = [
            ['name' => 'Filling', 'estimated_duration' => 45, 'duration_minutes' => 45, 'price' => 200, 'description' => 'Cavity filling procedure'],
            ['name' => 'Root Canal', 'estimated_duration' => 90, 'duration_minutes' => 90, 'price' => 500, 'description' => 'Root canal therapy'],
            ['name' => 'Implant Consultation', 'estimated_duration' => 60, 'duration_minutes' => 60, 'price' => 200, 'description' => 'Dental implant consultation'],
            ['name' => 'Whitening', 'estimated_duration' => 60, 'duration_minutes' => 60, 'price' => 300, 'description' => 'Professional teeth whitening'],
            ['name' => 'Orthodontic Consultation', 'estimated_duration' => 45, 'duration_minutes' => 45, 'price' => 150, 'description' => 'Braces consultation'],
        ];

        foreach ($additionalServices as $service) {
            Service::firstOrCreate(
                ['name' => $service['name']],
                array_merge($service, ['status' => 1])
            );
        }
        echo "✅ Created additional services\n";

        // 4. Create Additional Dentists
        echo "📝 Creating additional dentists...\n";
        $additionalDentists = [
            ['name' => 'Dr. James Wilson', 'specialization' => 'Implantology'],
            ['name' => 'Dr. Maria Garcia', 'specialization' => 'Cosmetic Dentistry'],
        ];

        foreach ($additionalDentists as $dentist) {
            Dentist::firstOrCreate(
                ['name' => $dentist['name']],
                array_merge($dentist, ['status' => 1])
            );
        }
        echo "✅ Created additional dentists\n";

        // 5. Create Appointments
        echo "📝 Creating appointments...\n";
        $appointmentCount = 0;
        $services = Service::all();
        $dentists = Dentist::all();

        if ($services->count() > 0 && $dentists->count() > 0) {
            // Past appointments (for retention testing)
            for ($i = 30; $i >= 5; $i--) {
                $appointmentDate = Carbon::today()->subDays($i);
                if (in_array($appointmentDate->format('l'), ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'])) {
                    $appointment = Appointment::create([
                        'patient_name' => $this->getFakeName(),
                        'patient_phone' => $this->getFakePhone(),
                        'patient_email' => $this->fakeEmail(),
                        'clinic_location' => 'seremban',
                        'service_id' => $services->random()->id,
                        'dentist_id' => $dentists->random()->id,
                        'appointment_date' => $appointmentDate,
                        'appointment_time' => $this->getFakeTime(),
                        'status' => 'completed',
                        'booking_source' => 'public',
                        'visit_token' => Str::uuid(),
                    ]);
                    $appointmentCount++;

                    // Add feedback for some completed appointments
                    if (rand(0, 1)) {
                        Feedback::create([
                            'appointment_id' => $appointment->id,
                            'patient_name' => $appointment->patient_name,
                            'patient_phone' => $appointment->patient_phone,
                            'rating' => rand(3, 5),
                            'comments' => $this->getFakeComments(),
                            'service_quality' => $this->getRandomQuality(),
                            'staff_friendliness' => $this->getRandomQuality(),
                            'cleanliness' => $this->getRandomQuality(),
                            'would_recommend' => rand(0, 1),
                        ]);
                    }
                }
            }

            // Today's appointments
            // CRITICAL: Ensure no room is assigned to multiple patients
            $roomsForToday = Room::where('clinic_location', 'seremban')->get();
            $assignedRoomIds = [];
            
            for ($i = 0; $i < 5; $i++) {
                // Determine appointment status based on queue position
                $appointmentStatus = match($i) {
                    0 => 'in_treatment',  // First appointment is being treated
                    1 => 'in_treatment',  // Second appointment is being treated
                    default => 'waiting', // Rest are waiting
                };
                
                $appointment = Appointment::create([
                    'patient_name' => $this->getFakeName(),
                    'patient_phone' => $this->getFakePhone(),
                    'patient_email' => $this->fakeEmail(),
                    'clinic_location' => 'seremban',
                    'service_id' => $services->random()->id,
                    'dentist_id' => $dentists->random()->id,
                    'appointment_date' => Carbon::today(),
                    'appointment_time' => sprintf('%02d:%02d:00', 9 + $i, 0),
                    'status' => $appointmentStatus,
                    'booking_source' => 'public',
                    'visit_token' => Str::uuid(),
                ]);
                $appointmentCount++;

                // Add queue entries for today
                $queueData = [
                    'queue_number' => $i + 1,
                    'queue_status' => $i === 0 ? 'in_treatment' : ($i === 1 ? 'in_treatment' : 'waiting'),
                    'check_in_time' => $i === 0 || $i === 1 ? now() : null,
                ];
                
                // CRITICAL: Assign dentist and room for in-treatment patients
                // ENFORCE ROOM EXCLUSIVITY: Each room can only be assigned to ONE patient at a time
                if ($i === 0 || $i === 1) {
                    $queueData['dentist_id'] = $appointment->dentist_id;
                    
                    // Find an unassigned room
                    $availableRoom = $roomsForToday->whereNotIn('id', $assignedRoomIds)->first();
                    if ($availableRoom) {
                        $queueData['room_id'] = $availableRoom->id;
                        $assignedRoomIds[] = $availableRoom->id; // Mark room as assigned
                        $availableRoom->markOccupied(); // Mark room status as occupied
                    }
                }
                
                Queue::firstOrCreate(
                    ['appointment_id' => $appointment->id],
                    $queueData
                );
            }

            // Upcoming appointments
            for ($i = 1; $i <= 15; $i++) {
                $appointmentDate = Carbon::today()->addDays($i);
                if (in_array($appointmentDate->format('l'), ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'])) {
                    Appointment::create([
                        'patient_name' => $this->getFakeName(),
                        'patient_phone' => $this->getFakePhone(),
                        'patient_email' => $this->fakeEmail(),
                        'clinic_location' => 'seremban',
                        'service_id' => $services->random()->id,
                        'dentist_id' => $dentists->random()->id,
                        'appointment_date' => $appointmentDate,
                        'appointment_time' => $this->getFakeTime(),
                        'status' => 'booked',
                        'booking_source' => 'public',
                        'visit_token' => Str::uuid(),
                    ]);
                    $appointmentCount++;
                }
            }
        }

        echo "✅ Created " . $appointmentCount . " appointments\n";

        // 6. Create Activity Logs
        echo "📝 Creating activity logs...\n";
        foreach (Appointment::inRandomOrder()->limit(20)->get() as $appointment) {
            ActivityLog::create([
                'action' => 'booked',
                'model_type' => 'Appointment',
                'model_id' => $appointment->id,
                'description' => 'Appointment booked by ' . $appointment->patient_name,
                'old_values' => null,
                'new_values' => json_encode($appointment->toArray()),
                'user_id' => $staff1->id,
                'user_name' => $staff1->name,
                'created_at' => $appointment->created_at,
            ]);
        }
        echo "✅ Created activity logs\n";

        echo "\n✅ All test data seeded successfully!\n";
        echo "\n📊 Data Summary:\n";
        echo "   • Users: " . User::count() . "\n";
        echo "   • Services: " . Service::count() . "\n";
        echo "   • Dentists: " . Dentist::count() . "\n";
        echo "   • Treatment Rooms: " . Room::count() . "\n";
        echo "   • Appointments: " . Appointment::count() . "\n";
        echo "   • Feedback: " . Feedback::count() . "\n";
        echo "   • Queues: " . Queue::count() . "\n";
        echo "   • Activity Logs: " . ActivityLog::count() . "\n";

        echo "\n🔑 Test Credentials:\n";
        echo "   Email: admin@clinic.com\n";
        echo "   Password: password123\n";
        echo "   Staff: staff1@clinic.com / staff2@clinic.com\n";

        echo "\n🌐 Test Links:\n";
        echo "   Public: http://localhost:8000/\n";
        echo "   Book: http://localhost:8000/book\n";
        echo "   Staff: http://localhost:8000/staff/appointments\n";
    }

    private function getFakeName(): string
    {
        $firstNames = ['Ahmed', 'Zainab', 'Muhammad', 'Fatima', 'Ali', 'Aisha', 'Omar', 'Layla', 'Hassan', 'Noor', 'Karim', 'Amira', 'Ibrahim', 'Hana', 'Rashid'];
        $lastNames = ['Abdullah', 'Hassan', 'Khan', 'Ahmed', 'Mohammed', 'Ibrahim', 'Ali', 'Malik', 'Rahman', 'Hussain'];
        
        return $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
    }

    private function getFakePhone(): string
    {
        $prefix = ['011', '012', '013', '014', '016', '017', '018', '019'];
        $number = $prefix[array_rand($prefix)] . rand(10000000, 99999999);
        return $number;
    }

    private function fakeEmail(): string
    {
        return Str::random(10) . '@example.com';
    }

    private function getFakeTime(): string
    {
        $hours = range(9, 17);
        $minutes = [0, 15, 30, 45];
        return sprintf('%02d:%02d:00', $hours[array_rand($hours)], $minutes[array_rand($minutes)]);
    }

    private function getFakeComments(): string
    {
        $comments = [
            'Great service and very professional staff!',
            'Excellent dentist, very friendly and caring.',
            'Clean clinic and comfortable environment.',
            'Would definitely recommend to friends and family.',
            'Very satisfied with the treatment.',
            'Professional and efficient service.',
            'The dentist explained everything clearly.',
            'Best dental clinic in town!',
        ];
        return $comments[array_rand($comments)];
    }

    private function getRandomQuality(): string
    {
        $qualities = ['excellent', 'good', 'fair', 'poor'];
        return $qualities[array_rand($qualities)];
    }
}
