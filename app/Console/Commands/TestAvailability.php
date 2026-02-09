<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Dentist;
use App\Models\DentistSchedule;
use App\Models\Appointment;
use Carbon\Carbon;

class TestAvailability extends Command
{
    protected $signature = 'test:availability {date?} {time?}';
    protected $description = 'Test dentist availability logic';

    public function handle()
    {
        $testDate = $this->argument('date') ?? Carbon::now()->addDay()->format('Y-m-d');
        $testTime = $this->argument('time') ?? '10:00';

        $this->info("=== Dentist Availability Test ===");
        $this->info("Test Date: $testDate");
        $this->info("Test Time: $testTime");
        $this->info("Day Name: " . Carbon::parse($testDate)->dayName);
        $this->newLine();

        // 1. Check if there are any dentists
        $this->info("1. DENTISTS IN DATABASE:");
        $dentists = Dentist::withoutGlobalScopes()->get();
        $this->info("Total dentists (including soft-deleted): " . $dentists->count());
        $activeDentists = Dentist::where('deleted_at', null)->get();
        $this->info("Active dentists: " . $activeDentists->count());
        $this->newLine();

        // 2. Check if there are any schedules
        $this->info("2. DENTIST SCHEDULES:");
        $scheduleCount = DentistSchedule::count();
        $this->info("Total schedules: $scheduleCount");
        $this->newLine();

        if ($scheduleCount > 0) {
            $this->info("3. SAMPLE SCHEDULES:");
            $samples = DentistSchedule::limit(3)->get();
            foreach ($samples as $schedule) {
                $this->info("  - Dentist ID {$schedule->dentist_id}: {$schedule->day_of_week} ({$schedule->start_time} - {$schedule->end_time})");
            }
            $this->newLine();
        }

        // 3. Test the availability check logic for one dentist
        if ($activeDentists->count() > 0) {
            $this->info("4. TESTING AVAILABILITY LOGIC:");
            $dentist = $activeDentists->first();
            $this->info("Testing with dentist: {$dentist->name} (ID: {$dentist->id})");
            
            $schedules = $dentist->schedules;
            $this->info("This dentist has " . $schedules->count() . " schedules");
            
            if ($schedules->count() > 0) {
                $dayOfWeek = Carbon::parse($testDate)->dayName;
                $this->info("Looking for schedule on: $dayOfWeek");
                $schedule = $schedules->where('day_of_week', $dayOfWeek)->first();
                
                if ($schedule) {
                    $this->info("✓ Found schedule for $dayOfWeek: {$schedule->start_time} - {$schedule->end_time}");
                    
                    // Check time - Note: start_time and end_time are stored as H:i:s (with seconds)
                    try {
                        $appointmentTime = Carbon::createFromFormat('H:i', $testTime);
                        $startTime = Carbon::createFromFormat('H:i:s', $schedule->start_time);
                        $endTime = Carbon::createFromFormat('H:i:s', $schedule->end_time);
                        
                        if ($appointmentTime->lt($startTime)) {
                            $this->warn("✗ Time $testTime is before start time {$schedule->start_time}");
                        } elseif ($appointmentTime->gt($endTime)) {
                            $this->warn("✗ Time $testTime is after end time {$schedule->end_time}");
                        } else {
                            $this->info("✓ Time $testTime is within working hours");
                        }
                    } catch (\Exception $e) {
                        $this->warn("✗ Error parsing time: " . $e->getMessage());
                    }
                } else {
                    $this->warn("✗ No schedule found for $dayOfWeek");
                    $this->info("  Available days: " . $schedules->pluck('day_of_week')->implode(', '));
                }
            }
        } else {
            $this->warn("✗ No active dentists found in database!");
        }

        // 4. Check for conflicting appointments at the test date/time
        $this->newLine();
        $this->info("5. CHECKING FOR CONFLICTS AT $testDate $testTime:");
        $conflicts = Appointment::where('appointment_date', $testDate)
            ->where('appointment_time', $testTime)
            ->whereNotIn('status', ['CANCELLED', 'NO_SHOW'])
            ->get();
        $this->info("Found " . $conflicts->count() . " conflicting appointments");

        $this->newLine();
        $this->info("=== End Test ===");
    }
}
