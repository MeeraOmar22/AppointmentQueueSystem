<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Queue;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AssignQueueNumbers extends Command
{
    protected $signature = 'queue:assign-today {date? : Date in Y-m-d, defaults to today}';
    protected $description = 'Assign queue numbers for appointments on a given date (default today)';

    public function handle(): int
    {
        $date = $this->argument('date') ? Carbon::parse($this->argument('date')) : Carbon::today();

        $appointments = Appointment::with(['queue', 'service'])
            ->whereDate('appointment_date', $date)
            ->orderBy('appointment_time')
            ->get();

        $counter = Queue::nextNumberForDate($date, true);

        foreach ($appointments as $appointment) {
            $queue = Queue::firstOrNew(['appointment_id' => $appointment->id]);

            if (!$queue->queue_number) {
                $queue->queue_number = $counter++;
            }

            if (!$queue->queue_status) {
                $queue->queue_status = 'waiting';
            }

            $queue->save();
        }

        $this->info('Queue numbers assigned for ' . $date->toDateString());
        return Command::SUCCESS;
    }
}
