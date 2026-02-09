<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\Dentist;
use App\Models\Service;
use App\Models\Appointment;
use App\Models\Queue;
use Carbon\Carbon;

class QueueTestDataSimpleSeeder extends Seeder
{
    public function run(): void
    {
        echo "\n════════════════════════════════════════════════════════\n";
        echo "  🧪 QUEUE TEST DATA SEEDER - EXECUTION\n";
        echo "════════════════════════════════════════════════════════\n\n";

        // Disable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Clear existing test data
        Queue::truncate();
        Appointment::truncate();
        Room::truncate();
        Dentist::truncate();
        Service::truncate();

        // Re-enable foreign key constraints
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        echo "🗑️  Cleared previous test data\n";

        // Create services with different durations
        $checkup = Service::create([
            'name' => 'Regular Checkup',
            'description' => 'Routine dental checkup',
            'estimated_duration' => 15,
            'price' => 50.00,
        ]);

        $cleaning = Service::create([
            'name' => 'Professional Cleaning',
            'description' => 'Deep cleaning and scaling',
            'estimated_duration' => 30,
            'price' => 80.00,
        ]);

        $filling = Service::create([
            'name' => 'Cavity Filling',
            'description' => 'Fill and treat cavity',
            'estimated_duration' => 45,
            'price' => 120.00,
        ]);

        $extraction = Service::create([
            'name' => 'Tooth Extraction',
            'description' => 'Extract damaged tooth',
            'estimated_duration' => 60,
            'price' => 150.00,
        ]);

        echo "📋 Created 4 services: Checkup(15m), Cleaning(30m), Filling(45m), Extraction(60m)\n";

        // Create dentists
        $dentist1 = Dentist::create([
            'name' => 'Dr. Ahmad',
            'email' => 'ahmad@clinic.com',
            'phone' => '0123456789',
            'specialization' => 'General Dentistry',
            'status' => 1,
        ]);

        $dentist2 = Dentist::create([
            'name' => 'Dr. Sarah',
            'email' => 'sarah@clinic.com',
            'phone' => '0123456790',
            'specialization' => 'Orthodontics',
            'status' => 1,
        ]);

        $dentist3 = Dentist::create([
            'name' => 'Dr. Ravi',
            'email' => 'ravi@clinic.com',
            'phone' => '0123456791',
            'specialization' => 'Oral Surgery',
            'status' => 1,
        ]);

        echo "👨‍⚕️  Created 3 dentists: Dr. Ahmad, Dr. Sarah, Dr. Ravi\n";

        // Create rooms
        $roomA1 = Room::create([
            'room_number' => 'A1',
            'clinic_location' => 'seremban',
            'status' => 'available',
            'is_active' => true,
        ]);

        $roomA2 = Room::create([
            'room_number' => 'A2',
            'clinic_location' => 'seremban',
            'status' => 'available',
            'is_active' => true,
        ]);

        $roomA3 = Room::create([
            'room_number' => 'A3',
            'clinic_location' => 'seremban',
            'status' => 'available',
            'is_active' => true,
        ]);

        echo "🏥 Created 3 treatment rooms: A1, A2, A3\n";

        echo "\n📍 Creating test appointments and queue entries...\n\n";

        // Patient 1: John Doe - In Treatment (Room A1)
        $p1 = Appointment::create([
            'patient_name' => 'John Doe',
            'patient_phone' => '0187654321',
            'appointment_date' => Carbon::today(),
            'appointment_time' => '09:00',
            'service_id' => $cleaning->id,
            'dentist_id' => $dentist1->id,
            'status' => 'in_treatment',
            'clinic_location' => 'seremban',
            'check_in_time' => Carbon::now()->subMinutes(15),
            'actual_start_time' => Carbon::now()->subMinutes(15),
            'visit_code' => 'ABC001',
        ]);

        Queue::create([
            'appointment_id' => $p1->id,
            'queue_number' => 1,
            'queue_status' => 'in_treatment',
            'room_id' => $roomA1->id,
            'dentist_id' => $dentist1->id,
            'check_in_time' => Carbon::now()->subMinutes(15),
        ]);

        echo "✓ Patient 1: John Doe (ABC001)\n";
        echo "  Status: IN TREATMENT | Room: A1\n";
        echo "  Service: Cleaning (30m) | Elapsed: 15m\n\n";

        // Patient 2: Jane Smith - In Treatment (Room A2)
        $p2 = Appointment::create([
            'patient_name' => 'Jane Smith',
            'patient_phone' => '0187654322',
            'appointment_date' => Carbon::today(),
            'appointment_time' => '09:30',
            'service_id' => $filling->id,
            'dentist_id' => $dentist2->id,
            'status' => 'in_treatment',
            'clinic_location' => 'seremban',
            'check_in_time' => Carbon::now()->subMinutes(10),
            'actual_start_time' => Carbon::now()->subMinutes(10),
            'visit_code' => 'ABC002',
        ]);

        Queue::create([
            'appointment_id' => $p2->id,
            'queue_number' => 2,
            'queue_status' => 'in_treatment',
            'room_id' => $roomA2->id,
            'dentist_id' => $dentist2->id,
            'check_in_time' => Carbon::now()->subMinutes(10),
        ]);

        echo "✓ Patient 2: Jane Smith (ABC002)\n";
        echo "  Status: IN TREATMENT | Room: A2\n";
        echo "  Service: Filling (45m) | Elapsed: 10m\n\n";

        // Patient 3: Mike Johnson - In Treatment (Room A3)
        $p3 = Appointment::create([
            'patient_name' => 'Mike Johnson',
            'patient_phone' => '0187654323',
            'appointment_date' => Carbon::today(),
            'appointment_time' => '09:45',
            'service_id' => $extraction->id,
            'dentist_id' => $dentist3->id,
            'status' => 'in_treatment',
            'clinic_location' => 'seremban',
            'check_in_time' => Carbon::now()->subMinutes(5),
            'actual_start_time' => Carbon::now()->subMinutes(5),
            'visit_code' => 'ABC003',
        ]);

        Queue::create([
            'appointment_id' => $p3->id,
            'queue_number' => 3,
            'queue_status' => 'in_treatment',
            'room_id' => $roomA3->id,
            'dentist_id' => $dentist3->id,
            'check_in_time' => Carbon::now()->subMinutes(5),
        ]);

        echo "✓ Patient 3: Mike Johnson (ABC003)\n";
        echo "  Status: IN TREATMENT | Room: A3\n";
        echo "  Service: Extraction (60m) | Elapsed: 5m\n\n";

        // Patient 4: Alice Brown - Waiting (Queue Position 4)
        $p4 = Appointment::create([
            'patient_name' => 'Alice Brown',
            'patient_phone' => '0187654324',
            'appointment_date' => Carbon::today(),
            'appointment_time' => '10:00',
            'service_id' => $cleaning->id,
            'dentist_id' => $dentist1->id,
            'status' => 'waiting',
            'clinic_location' => 'seremban',
            'check_in_time' => Carbon::now()->subMinutes(20),
            'visit_code' => 'ABC004',
        ]);

        Queue::create([
            'appointment_id' => $p4->id,
            'queue_number' => 4,
            'queue_status' => 'waiting',
            'check_in_time' => Carbon::now()->subMinutes(20),
        ]);

        echo "✓ Patient 4: Alice Brown (ABC004)\n";
        echo "  Status: WAITING | Queue Position: 4\n";
        echo "  Service: Cleaning (30m) | Expected ETA: ~30-40 minutes\n\n";

        // Patient 5: Bob Wilson - Waiting (Queue Position 5)
        $p5 = Appointment::create([
            'patient_name' => 'Bob Wilson',
            'patient_phone' => '0187654325',
            'appointment_date' => Carbon::today(),
            'appointment_time' => '10:15',
            'service_id' => $checkup->id,
            'dentist_id' => $dentist2->id,
            'status' => 'waiting',
            'clinic_location' => 'seremban',
            'check_in_time' => Carbon::now()->subMinutes(15),
            'visit_code' => 'ABC005',
        ]);

        Queue::create([
            'appointment_id' => $p5->id,
            'queue_number' => 5,
            'queue_status' => 'waiting',
            'check_in_time' => Carbon::now()->subMinutes(15),
        ]);

        echo "✓ Patient 5: Bob Wilson (ABC005)\n";
        echo "  Status: WAITING | Queue Position: 5\n";
        echo "  Service: Checkup (15m) | Expected ETA: ~45-60 minutes\n\n";

        // Patient 6: Carol Davis - Booked
        $p6 = Appointment::create([
            'patient_name' => 'Carol Davis',
            'patient_phone' => '0187654326',
            'appointment_date' => Carbon::today(),
            'appointment_time' => '11:00',
            'service_id' => $filling->id,
            'dentist_id' => $dentist3->id,
            'status' => 'booked',
            'clinic_location' => 'seremban',
            'visit_code' => 'ABC006',
        ]);

        echo "✓ Patient 6: Carol Davis (ABC006)\n";
        echo "  Status: BOOKED (not checked in)\n";
        echo "  Service: Filling (45m)\n\n";

        // Patient 7: David Miller - Checked In
        $p7 = Appointment::create([
            'patient_name' => 'David Miller',
            'patient_phone' => '0187654327',
            'appointment_date' => Carbon::today(),
            'appointment_time' => '11:15',
            'service_id' => $extraction->id,
            'dentist_id' => $dentist1->id,
            'status' => 'checked_in',
            'clinic_location' => 'seremban',
            'check_in_time' => Carbon::now()->subMinutes(2),
            'visit_code' => 'ABC007',
        ]);

        echo "✓ Patient 7: David Miller (ABC007)\n";
        echo "  Status: CHECKED_IN (awaiting queue assignment)\n";
        echo "  Service: Extraction (60m)\n\n";

        // Display summary
        echo "════════════════════════════════════════════════════════\n";
        echo "✅ TEST DATA CREATED SUCCESSFULLY\n";
        echo "════════════════════════════════════════════════════════\n\n";

        echo "📊 SUMMARY:\n";
        echo "  • 7 Appointments created\n";
        echo "  • 5 Queue entries created\n";
        echo "  • 3 Rooms (A1, A2, A3)\n";
        echo "  • 3 Dentists (Dr. Ahmad, Dr. Sarah, Dr. Ravi)\n";
        echo "  • 4 Services (Checkup, Cleaning, Filling, Extraction)\n\n";

        echo "🧪 TEST SCENARIO:\n";
        echo "  ┌─────────────────────────────────────────┐\n";
        echo "  │ IN TREATMENT (3 rooms occupied)         │\n";
        echo "  ├─────────────────────────────────────────┤\n";
        echo "  │ Room A1: John Doe - 15m elapsed         │\n";
        echo "  │ Room A2: Jane Smith - 10m elapsed       │\n";
        echo "  │ Room A3: Mike Johnson - 5m elapsed      │\n";
        echo "  ├─────────────────────────────────────────┤\n";
        echo "  │ WAITING QUEUE                           │\n";
        echo "  ├─────────────────────────────────────────┤\n";
        echo "  │ Q4: Alice Brown - ~30-40 min ETA        │\n";
        echo "  │ Q5: Bob Wilson - ~45-60 min ETA         │\n";
        echo "  └─────────────────────────────────────────┘\n\n";

        echo "🌐 TEST THESE URLS:\n";
        echo "  • /track/ABC001 (John - In Treatment: 0 min)\n";
        echo "  • /track/ABC004 (Alice - Waiting: ~30-40 min)\n";
        echo "  • /track/ABC005 (Bob - Waiting: ~45-60 min)\n";
        echo "  • /staff/queue-board (Staff Dashboard)\n";
        echo "  • /queue-board (Public Queue Board)\n\n";

        echo "📝 VERIFICATION:\n";
        echo "  ✓ Verify same ETA on all interfaces\n";
        echo "  ✓ Queue ordering correct (4 before 5)\n";
        echo "  ✓ In-treatment shows 0 minutes\n";
        echo "  ✓ No errors in logs\n\n";

        echo "✨ Ready for testing!\n";
        echo "════════════════════════════════════════════════════════\n\n";
    }
}
