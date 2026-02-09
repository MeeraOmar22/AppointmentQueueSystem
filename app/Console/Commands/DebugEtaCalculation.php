<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Models\Queue;
use App\Models\Room;
use App\Services\EstimatedWaitTimeService;
use Carbon\Carbon;

class DebugEtaCalculation extends Command
{
    protected $signature = 'debug:eta';
    protected $description = 'Debug ETA calculation for waiting patients';

    public function handle(EstimatedWaitTimeService $etaService)
    {
        $this->line("\n════════════════════════════════════════════════════════");
        $this->line("  🔍 DEBUGGING ETA CALCULATION");
        $this->line("════════════════════════════════════════════════════════\n");

        // Get Alice's appointment
        $alice = Appointment::where('visit_code', 'ABC004')->first();
        if (!$alice) {
            $this->error("Alice not found!");
            return;
        }

        $this->line("PATIENT: Alice Brown (ABC004)");
        $this->line("  Appointment Status: " . $alice->status->value);
        $this->line("  Service: {$alice->service->name} ({$alice->service->estimated_duration} min)");
        $this->line("  Queue Number: {$alice->queue->queue_number}");
        $this->line("  Queue Status: {$alice->queue->queue_status}\n");

        // Show all queue entries
        $this->line("CURRENT QUEUE ENTRIES:");
        $queues = Queue::with('appointment', 'room', 'dentist')->orderBy('queue_number')->get();
        foreach ($queues as $q) {
            $patient = $q->appointment->patient_name;
            $room = $q->room_id ? "Room {$q->room->room_number}" : "No room assigned";
            $dentist = $q->dentist_id ? $q->dentist->name : "No dentist";
            $this->line("  Q{$q->queue_number}: {$patient} | Status: {$q->queue_status} | {$room} | {$dentist}");
        }

        // Count occupied rooms
        $this->line("\n ROOM OCCUPANCY:");
        $occupiedRooms = Queue::where('queue_status', 'in_treatment')->distinct('room_id')->get();
        $this->line("  In-treatment patients (occupying rooms):");
        foreach ($occupiedRooms as $q) {
            $patient = $q->appointment->patient_name;
            $room = $q->room->room_number;
            $elapsed = Carbon::parse($q->check_in_time)->diffInMinutes(Carbon::now());
            $remaining = $q->appointment->service->estimated_duration - $elapsed;
            $this->line("    - {$patient} in Room {$room} ({$remaining}m remaining)");
        }

        $occupiedCount = Queue::where('queue_status', 'in_treatment')->distinct('room_id')->count();
        $totalRooms = Room::where('is_active', true)->count();
        $availableRooms = max(0, $totalRooms - $occupiedCount);

        $this->line("  Occupied rooms: $occupiedCount");
        $this->line("  Total active rooms: $totalRooms");
        $this->line("  Available rooms: $availableRooms\n");

        // Count patients ahead
        $this->line("PATIENTS AHEAD OF ALICE:");
        $patientsAheadWaiting = Queue::where('queue_number', '<', $alice->queue->queue_number)
            ->where('queue_status', 'waiting')
            ->get();
        
        foreach ($patientsAheadWaiting as $q) {
            $this->line("  - Q{$q->queue_number}: {$q->appointment->patient_name} (waiting)");
        }
        $count = $patientsAheadWaiting->count();
        $this->line("  Total waiting ahead: $count\n");

        // Show ETA calculation
        $this->line("ETA CALCULATION:");
        $serviceDuration = $alice->service->estimated_duration;
        
        if ($availableRooms === 0) {
            $this->line("  Available rooms = 0 → Use max remaining treatment time");
            $maxRemaining = Queue::where('queue_status', 'in_treatment')->get()
                ->map(function ($q) {
                    $elapsed = Carbon::parse($q->check_in_time)->diffInMinutes(Carbon::now());
                    return max(0, $q->appointment->service->estimated_duration - $elapsed);
                })
                ->max() ?? 0;
            $eta = $maxRemaining + $serviceDuration;
            $this->line("  Max remaining time + service duration = $maxRemaining + $serviceDuration = $eta minutes");
        } else {
            $patientsAhead = $count;
            $eta = ceil($patientsAhead / $availableRooms) * $serviceDuration;
            $this->line("  Formula: ceil(patientsAhead / availableRooms) × serviceDuration");
            $this->line("  = ceil($patientsAhead / $availableRooms) × $serviceDuration");
            $this->line("  = ceil(" . ($patientsAhead / $availableRooms) . ") × $serviceDuration");
            $this->line("  = " . ceil($patientsAhead / $availableRooms) . " × $serviceDuration");
            $this->line("  = $eta minutes");
        }

        // Get actual ETA from service
        $actualEta = $etaService->getETAForAppointment($alice);
        $this->line("\nACTUAL ETA FROM SERVICE: $actualEta minutes\n");

        $this->line("════════════════════════════════════════════════════════\n");
    }
}
