<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Models\Queue;
use App\Models\Room;
use App\Services\EstimatedWaitTimeService;
use Carbon\Carbon;

class DebugBobEta extends Command
{
    protected $signature = 'debug:bob';
    protected $description = 'Debug ETA calculation for Bob Wilson';

    public function handle(EstimatedWaitTimeService $etaService)
    {
        $this->line("\n════════════════════════════════════════════════════════");
        $this->line("  🔍 DEBUGGING BOB'S ETA CALCULATION");
        $this->line("════════════════════════════════════════════════════════\n");

        // Get Bob's appointment
        $bob = Appointment::where('visit_code', 'ABC005')->first();
        if (!$bob) {
            $this->error("Bob not found!");
            return;
        }

        $this->line("PATIENT: Bob Wilson (ABC005)");
        $this->line("  Service: {$bob->service->name} ({$bob->service->estimated_duration} min)");
        $this->line("  Queue Number: {$bob->queue->queue_number}");
        $this->line("  Queue Status: {$bob->queue->queue_status}\n");

        // Show all queue entries with details
        $this->line("CURRENT QUEUE ENTRIES:");
        $queues = Queue::with('appointment', 'room')->orderBy('queue_number')->get();
        foreach ($queues as $q) {
            $patient = $q->appointment->patient_name;
            $serviceMin = $q->appointment->service->estimated_duration;
            $room = $q->room_id ? "Room {$q->room->room_number}" : "No room";
            
            if ($q->queue_status === 'in_treatment' && $q->check_in_time) {
                $elapsed = Carbon::parse($q->check_in_time)->diffInMinutes(Carbon::now());
                $remaining = max(0, $serviceMin - $elapsed);
                $this->line("  Q{$q->queue_number}: {$patient} ({$serviceMin}m service) | Status: {$q->queue_status} | {$room} | {$remaining}m remaining");
            } else {
                $this->line("  Q{$q->queue_number}: {$patient} ({$serviceMin}m service) | Status: {$q->queue_status} | {$room}");
            }
        }

        // Count waiting patients ahead of Bob
        $this->line("\n WAITING PATIENTS AHEAD OF BOB:");
        $waitingAhead = Queue::whereHas('appointment', function ($q) use ($bob) {
            $q->whereDate('appointment_date', $bob->appointment_date)
              ->where('clinic_location', $bob->clinic_location)
              ->where('status', '!=', Appointment::STATE_COMPLETED)
              ->where('status', '!=', Appointment::STATE_FEEDBACK_SENT);
        })
            ->where('queue_number', '<', $bob->queue->queue_number)
            ->where('queue_status', 'waiting')
            ->with('appointment')
            ->get();

        foreach ($waitingAhead as $q) {
            $this->line("  - Q{$q->queue_number}: {$q->appointment->patient_name} ({$q->appointment->service->estimated_duration}m)");
        }
        $this->line("  Total waiting ahead: " . $waitingAhead->count());

        // Room occupancy
        $this->line("\nROOM OCCUPANCY:");
        $occupiedRooms = Queue::where('queue_status', 'in_treatment')->distinct('room_id')->count();
        $totalRooms = Room::where('is_active', true)->count();
        $availableRooms = max(0, $totalRooms - $occupiedRooms);
        
        $this->line("  Occupied: {$occupiedRooms} | Total: {$totalRooms} | Available: {$availableRooms}");

        // Calculate expected wait
        $this->line("\nBOB'S EXPECTED WAIT TIME:");
        if ($availableRooms > 0 && $waitingAhead->count() > 0) {
            $alice = $waitingAhead->first();
            $this->line("  Alice is ahead with {$alice->appointment->service->estimated_duration}m service");
            $this->line("  Current formula: ceil({$waitingAhead->count()} / {$availableRooms}) × {$bob->service->estimated_duration} = {ceil($waitingAhead->count() / $availableRooms) * $bob->service->estimated_duration}m");
            $this->line("  ❌ BUT this uses Bob's service duration, not Alice's!");
            $this->line("  ✅ Should be: Alice's duration + Bob's position = {$alice->appointment->service->estimated_duration} + {$bob->service->estimated_duration} = " . ($alice->appointment->service->estimated_duration + $bob->service->estimated_duration) . "m");
        }

        $actualEta = $etaService->getETAForAppointment($bob);
        $this->line("\nACTUAL ETA FROM SERVICE: {$actualEta} minutes\n");

        $this->line("════════════════════════════════════════════════════════\n");
    }
}
