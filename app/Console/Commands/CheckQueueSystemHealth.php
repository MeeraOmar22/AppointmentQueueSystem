<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Models\Queue;
use App\Models\Room;
use App\Models\Dentist;
use Carbon\Carbon;

class CheckQueueSystemHealth extends Command
{
    protected $signature = 'queue:health {--date= : Check specific date (YYYY-MM-DD), default is today}';
    protected $description = 'Check health of queue system - diagnose assignment errors';

    public function handle()
    {
        $date = $this->option('date') ? Carbon::createFromFormat('Y-m-d', $this->option('date'))->startOfDay() : Carbon::today();
        
        $this->info("=== QUEUE SYSTEM HEALTH CHECK ===");
        $this->info("Date: " . $date->format('Y-m-d'));
        $this->newLine();

        // 1. Check Appointments
        $this->line("📋 APPOINTMENTS");
        $appointments = Appointment::whereDate('appointment_date', $date)->get();
        $this->info("Total appointments: {$appointments->count()}");

        $byStatus = $appointments->groupBy(fn($a) => $a->status->value)->map->count();
        foreach ($byStatus as $status => $count) {
            $this->line("  • {$status}: {$count}");
        }
        $this->newLine();

        // 2. Check Queues
        $this->line("🔢 QUEUES");
        $queues = Queue::whereHas('appointment', function($q) use ($date) {
            $q->whereDate('appointment_date', $date);
        })->get();
        $this->info("Total queue entries: {$queues->count()}");

        $byQueueStatus = $queues->groupBy('queue_status')->map->count();
        foreach ($byQueueStatus as $status => $count) {
            $this->line("  • {$status}: {$count}");
        }
        $this->newLine();

        // 3. Check Rooms
        $this->line("🏥 ROOMS");
        $rooms = Room::where('clinic_location', 'seremban')->where('is_active', true)->get();
        $this->info("Total active rooms: {$rooms->count()}");

        $roomsInTreatment = Queue::where('queue_status', 'in_treatment')
            ->whereHas('appointment', function($q) use ($date) {
                $q->whereDate('appointment_date', $date);
            })
            ->pluck('room_id')
            ->unique()
            ->count();
        $this->line("  • Rooms in treatment: {$roomsInTreatment}");
        $this->line("  • Rooms available: " . ($rooms->count() - $roomsInTreatment));

        foreach ($rooms as $room) {
            $inTreatment = Queue::where('queue_status', 'in_treatment')
                ->where('room_id', $room->id)
                ->whereHas('appointment', function($q) use ($date) {
                    $q->whereDate('appointment_date', $date);
                })
                ->exists();
            $status = $inTreatment ? "IN USE" : "AVAILABLE";
            $this->line("  • Room {$room->room_number}: {$status} (status={$room->status})");
        }
        $this->newLine();

        // 4. Check Dentists
        $this->line("👨‍⚕️ DENTISTS");
        $dentists = Dentist::all();
        $this->info("Total dentists: {$dentists->count()}");
        $available = $dentists->where('status', true)->count();
        $busy = $dentists->where('status', false)->count();
        $this->line("  • Available: {$available}");
        $this->line("  • Busy: {$busy}");

        foreach ($dentists as $dentist) {
            $status = $dentist->status ? "AVAILABLE" : "BUSY";
            // Check if they have active treatment today
            $inTreatment = Queue::where('queue_status', 'in_treatment')
                ->where('dentist_id', $dentist->id)
                ->whereHas('appointment', function($q) use ($date) {
                    $q->whereDate('appointment_date', $date);
                })
                ->exists();
            $note = $inTreatment ? " (treating patient)" : "";
            $this->line("  • {$dentist->name}: {$status}{$note}");
        }
        $this->newLine();

        // 5. Check for Waiting Patients
        $this->line("⏳ WAITING PATIENTS");
        $waiting = Queue::where('queue_status', 'waiting')
            ->whereHas('appointment', function($q) use ($date) {
                $q->whereDate('appointment_date', $date);
            })
            ->with('appointment')
            ->get();
        
        $this->info("Waiting to be called: {$waiting->count()}");
        foreach ($waiting as $queue) {
            $dentist = $queue->appointment->dentist ? $queue->appointment->dentist->name : "Not assigned";
            $service = $queue->appointment->service ? $queue->appointment->service->name : "Not assigned";
            $this->line("  • #{$queue->queue_number}: {$queue->appointment->patient_name} | {$service} | Dr. {$dentist}");
        }
        $this->newLine();

        // 6. Diagnostic Checks
        $this->line("🔍 DIAGNOSTIC CHECKS");
        
        // Check if any in_treatment queue doesn't have a room assigned
        $orphanedQueues = Queue::where('queue_status', 'in_treatment')
            ->whereNull('room_id')
            ->whereHas('appointment', function($q) use ($date) {
                $q->whereDate('appointment_date', $date);
            })
            ->count();
        
        if ($orphanedQueues > 0) {
            $this->error("⚠️  WARNING: {$orphanedQueues} in_treatment queue entries without room assigned!");
        } else {
            $this->info("✅ All in_treatment queues have rooms assigned");
        }

        // Check if any in_treatment queue doesn't have dentist assigned
        $orphanedDentists = Queue::where('queue_status', 'in_treatment')
            ->whereNull('dentist_id')
            ->whereHas('appointment', function($q) use ($date) {
                $q->whereDate('appointment_date', $date);
            })
            ->count();
        
        if ($orphanedDentists > 0) {
            $this->error("⚠️  WARNING: {$orphanedDentists} in_treatment queue entries without dentist assigned!");
        } else {
            $this->info("✅ All in_treatment queues have dentists assigned");
        }

        // Check for stale status columns
        $roomsWithStalStatus = Room::where('clinic_location', 'seremban')
            ->where('is_active', true)
            ->where('status', '!=', 'available')
            ->whereDoesntHave('queues', function($q) {
                $q->where('queue_status', 'in_treatment');
            })
            ->count();
        
        if ($roomsWithStalStatus > 0) {
            $this->warn("⚠️  WARNING: {$roomsWithStalStatus} rooms marked as occupied but have no active treatments!");
            $this->line("   These should be marked as 'available' to allow new assignments");
        } else {
            $this->info("✅ Room status columns are consistent with actual treatments");
        }

        // Can patients be called?
        if ($available > 0 && $rooms->count() > $roomsInTreatment && $waiting->count() > 0) {
            $this->info("✅ System ready: Can call next patient");
        } else {
            $this->warn("⚠️  Cannot call next patient:");
            if ($available === 0) $this->line("   • No dentists available");
            if ($rooms->count() <= $roomsInTreatment) $this->line("   • All rooms occupied");
            if ($waiting->count() === 0) $this->line("   • No waiting patients");
        }

        $this->newLine();
        $this->info("=== END HEALTH CHECK ===");
    }
}
