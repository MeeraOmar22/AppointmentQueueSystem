<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Queue;

class CheckQueueData extends Command
{
    protected $signature = 'check:queue';

    public function handle()
    {
        $queues = Queue::orderBy('queue_number')->with('appointment')->get();
        
        $this->line("\nQUEUE DATABASE CONTENTS:");
        foreach ($queues as $q) {
            $this->line("Q{$q->queue_number}: {$q->appointment->patient_name} | Status: {$q->queue_status} | Clinic: {$q->appointment->clinic_location} | Date: {$q->appointment->appointment_date}");
        }

        $alice = \App\Models\Appointment::where('visit_code', 'ABC004')->first();
        $bob = \App\Models\Appointment::where('visit_code', 'ABC005')->first();
        
        $this->line("\n\nALICE'S QUEUE INFO:");
        $this->line("Alice queue_number: {$alice->queue->queue_number}");
        $this->line("Clinic: {$alice->clinic_location}");
        $this->line("Date: {$alice->appointment_date}");
        
        $this->line("\n\nBOB'S QUEUE INFO:");
        $this->line("Bob queue_number: {$bob->queue->queue_number}");
        $this->line("Clinic: {$bob->clinic_location}");
        $this->line("Date: {$bob->appointment_date}");

        // Count patients ahead of Alice
        $patientsAhead = Queue::whereHas('appointment', function ($q) use ($alice) {
            $q->whereDate('appointment_date', $alice->appointment_date)
              ->where('clinic_location', $alice->clinic_location)
              ->where('status', '!=', \App\Models\Appointment::STATE_COMPLETED)
              ->where('status', '!=', \App\Models\Appointment::STATE_FEEDBACK_SENT);
        })
            ->where('queue_number', '<', $alice->queue->queue_number)
            ->whereIn('queue_status', ['waiting', 'in_treatment'])
            ->get();

        $this->line("\n\nPATIENTS AHEAD OF ALICE:");
        foreach ($patientsAhead as $q) {
            $this->line("Q{$q->queue_number}: {$q->appointment->patient_name} (queue_status: {$q->queue_status})");
        }
        $this->line("Total: " . $patientsAhead->count());
    }
}
