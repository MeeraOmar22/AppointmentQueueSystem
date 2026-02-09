<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use Illuminate\Console\Command;

class UpdateVisitCodes extends Command
{
    protected $signature = 'appointments:update-visit-codes';
    protected $description = 'Update missing visit codes for appointments';

    public function handle()
    {
        $appointments = Appointment::whereNull('visit_code')->orWhere('visit_code', '')->orWhere('visit_code', 'LIKE', '%  %')->get();
        
        $updated = 0;
        foreach ($appointments as $appointment) {
            try {
                $code = Appointment::generateVisitCode($appointment->appointment_date ?? now());
                $appointment->visit_code = $code;
                $appointment->save();
                $updated++;
            } catch (\Exception $e) {
                // Skip if unique constraint fails
                $this->warn('Skipped appointment ' . $appointment->id . ' (duplicate code)');
            }
        }

        $this->info('✅ Updated ' . $updated . ' appointments with visit codes');
        return 0;
    }
}
