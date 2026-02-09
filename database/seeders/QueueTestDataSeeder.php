<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\Dentist;
use App\Models\Service;
use App\Models\Appointment;
use App\Models\Queue;
use Carbon\Carbon;

class QueueTestDataSeeder extends Seeder
{
    /**
     * Seed the database with comprehensive queue test data
     * 
     * This seeder creates realistic scenarios for testing ETA calculations:
     * - Scenario 1: Single room with multiple waiting patients
     * - Scenario 2: Multiple rooms with concurrent treatment
     * - Scenario 3: All rooms occupied with waiting queue
     * - Scenario 4: Mixed statuses for comprehensive testing
     */
    public function run(): void
    {
        $clinicLocation = 'seremban';
        $today = Carbon::today();

        // Disable foreign key constraints
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Clear existing test data
        Queue::truncate();
        Appointment::truncate();
        Room::truncate();
        Dentist::truncate();
        Service::truncate();

        // Re-enable foreign key constraints
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Create services with different durations
        $services = [
            $checkup = Service::create([
                'name' => 'Regular Checkup',
                'description' => 'Routine dental checkup',
                'estimated_duration' => 15,
                'price' => 50.00,
            ]),
            $cleaning = Service::create([
                'name' => 'Professional Cleaning',
                'description' => 'Deep cleaning and scaling',
                'estimated_duration' => 30,
                'price' => 80.00,
            ]),
            $filling = Service::create([
                'name' => 'Cavity Filling',
                'description' => 'Fill and treat cavity',
                'estimated_duration' => 45,
                'price' => 120.00,
            ]),
            $extraction = Service::create([
                'name' => 'Tooth Extraction',
                'description' => 'Extract damaged tooth',
                'estimated_duration' => 60,
                'price' => 150.00,
            ]),
        ];

        // Create dentists
        $dentists = [
            Dentist::create([
                'name' => 'Dr. Ahmad',
                'email' => 'ahmad@clinic.com',
                'phone' => '0123456789',
                'specialization' => 'General Dentistry',
                'status' => 1,
            ]),
            Dentist::create([
                'name' => 'Dr. Sarah',
                'email' => 'sarah@clinic.com',
                'phone' => '0123456790',
                'specialization' => 'Orthodontics',
                'status' => 1,
            ]),
            Dentist::create([
                'name' => 'Dr. Ravi',
                'email' => 'ravi@clinic.com',
                'phone' => '0123456791',
                'specialization' => 'Oral Surgery',
                'status' => 1,
            ]),
        ];

        // Create rooms
        $rooms = [
            Room::create([
                'room_number' => 'A1',
                'clinic_location' => $clinicLocation,
                'status' => 'available',
                'is_active' => true,
            ]),
            Room::create([
                'room_number' => 'A2',
                'clinic_location' => $clinicLocation,
                'status' => 'available',
                'is_active' => true,
            ]),
            Room::create([
                'room_number' => 'A3',
                'clinic_location' => $clinicLocation,
                'status' => 'available',
                'is_active' => true,
            ]),
        ];

        echo "\n=== QUEUE TEST DATA SEEDING ===\n\n";

        // SCENARIO 1: Patient in treatment (Patient 1)
        echo "📍 Scenario 1: Patient in treatment\n";
        $patient1 = Appointment::create([
            'patient_name' => 'John Doe',
            'patient_phone' => '0187654321',
            'appointment_date' => $today,
            'appointment_time' => '09:00',
            'service_id' => $cleaning->id,
            'dentist_id' => $dentists[0]->id,
            'status' => 'in_treatment',
            'clinic_location' => $clinicLocation,
            'check_in_time' => now()->subMinutes(15),
            'actual_start_time' => now()->subMinutes(15),
            'visit_code' => 'ABC001',
        ]);
        
        Queue::create([
            'appointment_id' => $patient1->id,
            'queue_number' => 1,
            'queue_status' => 'in_treatment',
            'room_id' => $rooms[0]->id,
            'dentist_id' => $dentists[0]->id,
            'clinic_location' => $clinicLocation,
        ]);
        echo "   Patient 1 (John): IN TREATMENT - Room A1\n";
        echo "   Service: {$cleaning->name} ({$cleaning->estimated_duration} min)\n";
        echo "   Started: 15 minutes ago\n";

        // SCENARIO 2: Two more patients in other rooms
        echo "\n📍 Scenario 2: Multiple concurrent treatments\n";
        
        $patient2 = Appointment::create([
            'patient_name' => 'Jane Smith',
            'patient_phone' => '0187654322',
            'appointment_date' => $today,
            'appointment_time' => '09:30',
            'service_id' => $filling->id,
            'dentist_id' => $dentists[1]->id,
            'status' => 'in_treatment',
            'clinic_location' => $clinicLocation,
            'check_in_time' => now()->subMinutes(10),
            'actual_start_time' => now()->subMinutes(10),
            'visit_code' => 'ABC002',
        ]);
        
        Queue::create([
            'appointment_id' => $patient2->id,
            'queue_number' => 2,
            'queue_status' => 'in_treatment',
            'room_id' => $rooms[1]->id,
            'dentist_id' => $dentists[1]->id,
            'clinic_location' => $clinicLocation,
        ]);
        echo "   Patient 2 (Jane): IN TREATMENT - Room A2\n";
        echo "   Service: {$filling->name} ({$filling->estimated_duration} min)\n";
        echo "   Started: 10 minutes ago\n";

        $patient3 = Appointment::create([
            'patient_name' => 'Mike Johnson',
            'patient_phone' => '0187654323',
            'appointment_date' => $today,
            'appointment_time' => '09:45',
            'service_id' => $extraction->id,
            'dentist_id' => $dentists[2]->id,
            'status' => 'in_treatment',
            'clinic_location' => $clinicLocation,
            'check_in_time' => now()->subMinutes(5),
            'actual_start_time' => now()->subMinutes(5),
            'visit_code' => 'ABC003',
        ]);
        
        Queue::create([
            'appointment_id' => $patient3->id,
            'queue_number' => 3,
            'queue_status' => 'in_treatment',
            'room_id' => $rooms[2]->id,
            'dentist_id' => $dentists[2]->id,
            'clinic_location' => $clinicLocation,
        ]);
        echo "   Patient 3 (Mike): IN TREATMENT - Room A3\n";
        echo "   Service: {$extraction->name} ({$extraction->estimated_duration} min)\n";
        echo "   Started: 5 minutes ago\n";

        // SCENARIO 3: Waiting patients in queue
        echo "\n📍 Scenario 3: Patients waiting in queue\n";
        
        $patient4 = Appointment::create([
            'patient_name' => 'Alice Brown',
            'patient_phone' => '0187654324',
            'appointment_date' => $today,
            'appointment_time' => '10:00',
            'service_id' => $cleaning->id,
            'dentist_id' => null,
            'status' => 'waiting',
            'clinic_location' => $clinicLocation,
            'check_in_time' => now()->subMinutes(2),
            'visit_code' => 'ABC004',
        ]);
        
        Queue::create([
            'appointment_id' => $patient4->id,
            'queue_number' => 4,
            'queue_status' => 'waiting',
            'room_id' => null,
            'dentist_id' => null,
            'clinic_location' => $clinicLocation,
        ]);
        echo "   Patient 4 (Alice): WAITING - Position #4\n";
        echo "   Service: {$cleaning->name} ({$cleaning->estimated_duration} min)\n";
        echo "   ⏱️  ETA: Depends on rooms becoming available\n";

        $patient5 = Appointment::create([
            'patient_name' => 'Bob Wilson',
            'patient_phone' => '0187654325',
            'appointment_date' => $today,
            'appointment_time' => '10:15',
            'service_id' => $checkup->id,
            'dentist_id' => null,
            'status' => 'waiting',
            'clinic_location' => $clinicLocation,
            'check_in_time' => now()->subMinutes(1),
            'visit_code' => 'ABC005',
        ]);
        
        Queue::create([
            'appointment_id' => $patient5->id,
            'queue_number' => 5,
            'queue_status' => 'waiting',
            'room_id' => null,
            'dentist_id' => null,
            'clinic_location' => $clinicLocation,
        ]);
        echo "   Patient 5 (Bob): WAITING - Position #5\n";
        echo "   Service: {$checkup->name} ({$checkup->estimated_duration} min)\n";
        echo "   ⏱️  ETA: Depends on rooms becoming available\n";

        // SCENARIO 4: Booked appointments (not checked in yet)
        echo "\n📍 Scenario 4: Booked appointments\n";
        
        $patient6 = Appointment::create([
            'patient_name' => 'Carol Davis',
            'patient_phone' => '0187654326',
            'appointment_date' => $today,
            'appointment_time' => '11:00',
            'service_id' => $filling->id,
            'dentist_id' => null,
            'status' => 'booked',
            'clinic_location' => $clinicLocation,
            'visit_code' => 'ABC006',
        ]);
        echo "   Patient 6 (Carol): BOOKED\n";
        echo "   Service: {$filling->name} ({$filling->estimated_duration} min)\n";
        echo "   Status: Not yet checked in\n";

        // SCENARIO 5: Checked in but not yet in queue
        echo "\n📍 Scenario 5: Checked in (waiting to enter queue)\n";
        
        $patient7 = Appointment::create([
            'patient_name' => 'David Miller',
            'patient_phone' => '0187654327',
            'appointment_date' => $today,
            'appointment_time' => '11:30',
            'service_id' => $extraction->id,
            'dentist_id' => null,
            'status' => 'checked_in',
            'clinic_location' => $clinicLocation,
            'check_in_time' => now(),
            'visit_code' => 'ABC007',
        ]);
        echo "   Patient 7 (David): CHECKED IN\n";
        echo "   Service: {$extraction->name} ({$extraction->estimated_duration} min)\n";
        echo "   Status: Just checked in, waiting to be called\n";

        echo "\n=== TEST DATA SUMMARY ===\n\n";
        echo "✅ Dentists created: " . count($dentists) . "\n";
        echo "✅ Rooms created: " . count($rooms) . "\n";
        echo "✅ Services created: " . count($services) . "\n";
        echo "✅ Appointments created: 7\n";
        echo "✅ Queue entries created: 5\n\n";

        echo "📊 CURRENT QUEUE STATUS:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "IN TREATMENT: 3 patients\n";
        echo "  - Room A1: John Doe (Cleaning, 15 min ago)\n";
        echo "  - Room A2: Jane Smith (Filling, 10 min ago)\n";
        echo "  - Room A3: Mike Johnson (Extraction, 5 min ago)\n\n";
        echo "WAITING: 2 patients\n";
        echo "  - Position 4: Alice Brown (Cleaning)\n";
        echo "  - Position 5: Bob Wilson (Checkup)\n\n";
        echo "OTHER:\n";
        echo "  - Booked: Carol Davis (Filling)\n";
        echo "  - Checked in: David Miller (Extraction)\n\n";

        echo "🧪 TEST SCENARIOS READY:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "1️⃣  Single room with patients (Room A1)\n";
        echo "2️⃣  Multiple concurrent treatments (all 3 rooms)\n";
        echo "3️⃣  Waiting queue when all rooms occupied\n";
        echo "4️⃣  ETA calculation with mixed durations\n";
        echo "5️⃣  Different appointment statuses\n\n";

        echo "📝 NEXT STEPS:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "1. Run seeder: php artisan db:seed --class=QueueTestDataSeeder\n";
        echo "2. Visit tracking page: /track/ABC004 (Alice's visit code)\n";
        echo "3. Check staff dashboard: /staff/queue-board\n";
        echo "4. View public queue: /queue-board\n";
        echo "5. Verify ETA consistency across pages\n\n";
    }
}
