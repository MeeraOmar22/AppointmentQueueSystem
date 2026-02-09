<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\Dentist;
use App\Models\Service;
use App\Models\Appointment;
use App\Models\Queue;
use App\Models\User;
use Carbon\Carbon;

class AppointmentDataRefreshSeeder extends Seeder
{
    public function run(): void
    {
        echo "\n════════════════════════════════════════════════════════\n";
        echo "  🔄 APPOINTMENT DATA REFRESH - COMPLETE RESET\n";
        echo "════════════════════════════════════════════════════════\n\n";

        // Disable foreign key checks
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Clear all appointment-related data
        Queue::truncate();
        Appointment::truncate();

        // Re-enable foreign key constraints
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        echo "✅ Cleared all appointment and queue data\n";

        // Verify we have necessary base data
        $services = Service::all();
        if ($services->isEmpty()) {
            echo "❌ No services found. Running ServiceSeeder first...\n";
            $this->call(ServiceSeeder::class);
            $services = Service::all();
        }

        $dentists = Dentist::all();
        if ($dentists->isEmpty()) {
            echo "❌ No dentists found. Running DentistSeeder first...\n";
            $this->call(DentistSeeder::class);
            $dentists = Dentist::all();
        }

        $rooms = Room::all();
        if ($rooms->isEmpty()) {
            echo "❌ No rooms found. Running RoomSeeder first...\n";
            $this->call(RoomSeeder::class);
            $rooms = Room::all();
        }

        // Get or create patient users
        $users = User::where('role', 'patient')->get();
        if ($users->isEmpty()) {
            echo "⚠️  No patient users found. Creating demo patients...\n";
            for ($i = 1; $i <= 7; $i++) {
                User::create([
                    'name' => 'Patient ' . $i,
                    'email' => 'patient' . $i . '@example.com',
                    'password' => bcrypt('password'),
                    'role' => 'patient',
                    'phone' => '555-010' . $i,
                ]);
            }
            $users = User::where('role', 'patient')->get();
        }

        echo "✅ Base data verified: " . $services->count() . " services, " . $dentists->count() . " dentists, " . $rooms->count() . " rooms, " . $users->count() . " patients\n\n";

        // Create fresh appointments with visit codes
        $today = Carbon::now();
        $appointmentCount = 0;

        // Appointment 1: Alice Brown - Regular Checkup
        $appointment1 = Appointment::create([
            'user_id' => $users->first()->id,
            'patient_name' => 'Alice Brown',
            'patient_phone' => '555-0101',
            'patient_email' => 'alice@example.com',
            'clinic_location' => 'seremban',
            'service_id' => $services->where('name', 'Regular Checkup')->first()->id,
            'dentist_id' => $dentists->first()->id,
            'dentist_preference' => $dentists->first()->name,
            'room' => $rooms->first()->id,
            'appointment_date' => $today->copy()->toDateString(),
            'appointment_time' => '09:00',
            'start_at' => $today->copy()->setHour(9)->setMinute(0),
            'end_at' => $today->copy()->setHour(9)->setMinute(15),
            'status' => 'completed',
            'visit_code' => 'VIS001',
            'visit_token' => $this->generateVisitToken(),
            'booking_source' => 'public',
            'checked_in_at' => $today->copy()->setHour(9)->setMinute(0),
            'check_in_time' => $today->copy()->setHour(9)->setMinute(0),
            'treatment_started_at' => $today->copy()->setHour(9)->setMinute(0),
            'treatment_ended_at' => $today->copy()->setHour(9)->setMinute(15),
        ]);
        $appointmentCount++;
        echo "✅ Created Appointment 1: Alice Brown - VIS001 (Completed)\n";

        // Appointment 2: Bob Smith - Professional Cleaning
        $appointment2 = Appointment::create([
            'user_id' => $users->skip(1)->first()->id ?? $users->first()->id,
            'patient_name' => 'Bob Smith',
            'patient_phone' => '555-0102',
            'patient_email' => 'bob@example.com',
            'clinic_location' => 'Main Clinic',
            'service_id' => $services->where('name', 'Professional Cleaning')->first()->id,
            'dentist_id' => $dentists->skip(1)->first()->id ?? $dentists->first()->id,
            'dentist_preference' => ($dentists->skip(1)->first() ?? $dentists->first())->name,
            'room' => $rooms->skip(1)->first()->id ?? $rooms->first()->id,
            'appointment_date' => $today->copy()->toDateString(),
            'appointment_time' => '09:30',
            'start_at' => $today->copy()->setHour(9)->setMinute(30),
            'end_at' => $today->copy()->setHour(10)->setMinute(0),
            'status' => 'in_treatment',
            'visit_code' => 'VIS002',
            'visit_token' => $this->generateVisitToken(),
            'booking_source' => 'public',
            'checked_in_at' => $today->copy()->setHour(9)->setMinute(30),
            'check_in_time' => $today->copy()->setHour(9)->setMinute(25),
            'treatment_started_at' => $today->copy()->setHour(9)->setMinute(30),
        ]);
        $appointmentCount++;
        echo "✅ Created Appointment 2: Bob Smith - VIS002 (In Treatment)\n";

        // Appointment 3: Carol Davis - Root Canal
        $appointment3 = Appointment::create([
            'user_id' => $users->skip(2)->first()->id ?? $users->first()->id,
            'patient_name' => 'Carol Davis',
            'patient_phone' => '555-0103',
            'patient_email' => 'carol@example.com',
            'clinic_location' => 'Main Clinic',
            'service_id' => $services->where('name', 'Root Canal Treatment')->first()->id ?? $services->skip(2)->first()->id,
            'dentist_id' => $dentists->skip(2)->first()->id ?? $dentists->first()->id,
            'dentist_preference' => ($dentists->skip(2)->first() ?? $dentists->first())->name,
            'room' => $rooms->skip(2)->first()->id ?? $rooms->first()->id,
            'appointment_date' => $today->copy()->toDateString(),
            'appointment_time' => '10:00',
            'start_at' => $today->copy()->setHour(10)->setMinute(0),
            'end_at' => $today->copy()->setHour(10)->setMinute(45),
            'status' => 'waiting',
            'visit_code' => 'VIS003',
            'visit_token' => $this->generateVisitToken(),
            'booking_source' => 'public',
            'checked_in_at' => $today->copy()->setHour(10)->setMinute(0),
            'check_in_time' => $today->copy()->setHour(10)->setMinute(0),
        ]);
        $appointmentCount++;
        echo "✅ Created Appointment 3: Carol Davis - VIS003 (Waiting)\n";

        // Appointment 4: David Wilson - Filling
        $appointment4 = Appointment::create([
            'user_id' => $users->skip(3)->first()->id ?? $users->first()->id,
            'patient_name' => 'David Wilson',
            'patient_phone' => '555-0104',
            'patient_email' => 'david@example.com',
            'clinic_location' => 'Main Clinic',
            'service_id' => $services->where('name', 'Filling')->first()->id ?? $services->skip(3)->first()->id,
            'dentist_id' => $dentists->first()->id,
            'dentist_preference' => $dentists->first()->name,
            'room' => $rooms->first()->id,
            'appointment_date' => $today->copy()->toDateString(),
            'appointment_time' => '10:15',
            'start_at' => $today->copy()->setHour(10)->setMinute(15),
            'end_at' => $today->copy()->setHour(10)->setMinute(35),
            'status' => 'waiting',
            'visit_code' => 'VIS004',
            'visit_token' => $this->generateVisitToken(),
            'booking_source' => 'public',
            'checked_in_at' => $today->copy()->setHour(10)->setMinute(10),
            'check_in_time' => $today->copy()->setHour(10)->setMinute(10),
        ]);
        $appointmentCount++;
        echo "✅ Created Appointment 4: David Wilson - VIS004 (Waiting)\n";

        // Appointment 5: Emily Johnson - Extraction
        $appointment5 = Appointment::create([
            'user_id' => $users->skip(4)->first()->id ?? $users->first()->id,
            'patient_name' => 'Emily Johnson',
            'patient_phone' => '555-0105',
            'patient_email' => 'emily@example.com',
            'clinic_location' => 'Main Clinic',
            'service_id' => $services->last()->id,
            'dentist_id' => $dentists->skip(1)->first()->id ?? $dentists->first()->id,
            'dentist_preference' => ($dentists->skip(1)->first() ?? $dentists->first())->name,
            'room' => $rooms->skip(1)->first()->id ?? $rooms->first()->id,
            'appointment_date' => $today->copy()->toDateString(),
            'appointment_time' => '10:30',
            'start_at' => $today->copy()->setHour(10)->setMinute(30),
            'end_at' => $today->copy()->setHour(11)->setMinute(0),
            'status' => 'booked',
            'visit_code' => 'VIS005',
            'visit_token' => $this->generateVisitToken(),
            'booking_source' => 'public',
            'checked_in_at' => null,
        ]);
        $appointmentCount++;
        echo "✅ Created Appointment 5: Emily Johnson - VIS005 (Booked)\n";

        // Appointment 6: Frank Martinez - Scaling & Root Planing
        $appointment6 = Appointment::create([
            'user_id' => $users->skip(5)->first()->id ?? $users->first()->id,
            'patient_name' => 'Frank Martinez',
            'patient_phone' => '555-0106',
            'patient_email' => 'frank@example.com',
            'clinic_location' => 'Main Clinic',
            'service_id' => $services->skip(1)->first()->id,
            'dentist_id' => $dentists->skip(2)->first()->id ?? $dentists->first()->id,
            'dentist_preference' => ($dentists->skip(2)->first() ?? $dentists->first())->name,
            'room' => $rooms->skip(2)->first()->id ?? $rooms->first()->id,
            'appointment_date' => $today->copy()->addDay()->toDateString(),
            'appointment_time' => '14:00',
            'start_at' => $today->copy()->addDay()->setHour(14)->setMinute(0),
            'end_at' => $today->copy()->addDay()->setHour(14)->setMinute(45),
            'status' => 'booked',
            'visit_code' => 'VIS006',
            'visit_token' => $this->generateVisitToken(),
            'booking_source' => 'public',
            'checked_in_at' => null,
        ]);
        $appointmentCount++;
        echo "✅ Created Appointment 6: Frank Martinez - VIS006 (Booked - Tomorrow)\n";

        // Appointment 7: Grace Lee - Whitening
        $appointment7 = Appointment::create([
            'user_id' => $users->skip(6)->first()->id ?? $users->first()->id,
            'patient_name' => 'Grace Lee',
            'patient_phone' => '555-0107',
            'patient_email' => 'grace@example.com',
            'clinic_location' => 'Main Clinic',
            'service_id' => $services->skip(3)->first()->id,
            'dentist_id' => $dentists->first()->id,
            'dentist_preference' => $dentists->first()->name,
            'room' => $rooms->first()->id,
            'appointment_date' => $today->copy()->addDays(2)->toDateString(),
            'appointment_time' => '16:00',
            'start_at' => $today->copy()->addDays(2)->setHour(16)->setMinute(0),
            'end_at' => $today->copy()->addDays(2)->setHour(16)->setMinute(30),
            'status' => 'booked',
            'visit_code' => 'VIS007',
            'visit_token' => $this->generateVisitToken(),
            'booking_source' => 'public',
            'checked_in_at' => null,
        ]);
        $appointmentCount++;
        echo "✅ Created Appointment 7: Grace Lee - VIS007 (Booked - In 2 days)\n";

        // Create queue entries for checked-in appointments
        foreach ([$appointment1, $appointment2, $appointment3, $appointment4] as $apt) {
            if ($apt->checked_in_at) {
                Queue::create([
                    'appointment_id' => $apt->id,
                    'queue_number' => Queue::count() + 1,
                    'queue_status' => $apt->status === 'completed' ? 'completed' : ($apt->status === 'in_treatment' ? 'in_treatment' : 'waiting'),
                    'check_in_time' => $apt->checked_in_at,
                    'room_id' => $apt->room,
                    'dentist_id' => $apt->dentist_id,
                ]);
            }
        }

        echo "\n════════════════════════════════════════════════════════\n";
        echo "  ✅ APPOINTMENT DATA REFRESH COMPLETE!\n";
        echo "════════════════════════════════════════════════════════\n";
        echo "📊 Summary:\n";
        echo "   • Appointments Created: $appointmentCount\n";
        echo "   • Queue Entries: " . Queue::count() . "\n";
        echo "   • Date Range: " . $today->format('Y-m-d') . " to " . $today->copy()->addDays(2)->format('Y-m-d') . "\n";
        echo "   • Visit Codes: VIS001 through VIS00" . $appointmentCount . "\n";
        echo "════════════════════════════════════════════════════════\n\n";
    }

    private function generateVisitToken(): string
    {
        return \Illuminate\Support\Str::uuid()->toString();
    }
}
