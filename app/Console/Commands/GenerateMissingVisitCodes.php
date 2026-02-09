<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;

class GenerateMissingVisitCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-missing-visit-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate missing visit codes for appointments that were created before the visit_code feature was added';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('✅ Generating missing visit codes for appointments...');

        // Find all appointments with null visit_code
        $appointmentsWithoutCodes = Appointment::whereNull('visit_code')->get();

        if ($appointmentsWithoutCodes->isEmpty()) {
            $this->info('✅ No appointments found without visit codes. All set!');
            return 0;
        }

        $count = 0;

        foreach ($appointmentsWithoutCodes as $appointment) {
            // Generate visit code with collision detection
            $baseDate = $appointment->appointment_date ?? $appointment->created_at;
            $dateStr = \Carbon\Carbon::parse($baseDate)->format('Ymd');
            
            // Find max sequence number for this date (including existing appointments)
            $lastSeq = Appointment::whereDate('appointment_date', \Carbon\Carbon::parse($baseDate)->toDateString())
                ->whereNotNull('visit_code')
                ->pluck('visit_code')
                ->map(fn($code) => (int)substr($code, -3)) // Extract last 3 digits
                ->max() ?? 0;
            
            $newSeq = $lastSeq + 1;
            $appointment->visit_code = sprintf('DNT-%s-%03d', $dateStr, $newSeq);
            $appointment->save();
            
            $this->line("  ✓ {$appointment->patient_name}: {$appointment->visit_code}");
            $count++;
        }

        $this->info("✅ Successfully generated {$count} missing visit codes!");
        return 0;
    }
}
