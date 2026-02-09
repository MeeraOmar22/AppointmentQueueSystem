<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Room;
use App\Models\Queue;
use Carbon\Carbon;

class FixStaleRoomStatus extends Command
{
    protected $signature = 'queue:fix-rooms {--date= : Fix rooms for specific date (YYYY-MM-DD), default is today}';
    protected $description = 'Fix stale room status - mark rooms as available if they have no active treatments';

    public function handle()
    {
        $date = $this->option('date') ? Carbon::createFromFormat('Y-m-d', $this->option('date'))->startOfDay() : Carbon::today();
        
        $this->info("🔧 Fixing stale room statuses for {$date->format('Y-m-d')}");
        $this->newLine();

        // Find all rooms marked as occupied
        $occupiedRooms = Room::where('status', 'occupied')
            ->where('is_active', true)
            ->get();

        if ($occupiedRooms->isEmpty()) {
            $this->info("✅ No rooms to fix - all occupied rooms have active treatments");
            return;
        }

        $fixed = 0;

        foreach ($occupiedRooms as $room) {
            // Check if this room has active treatment today
            $hasActiveTreatment = Queue::where('queue_status', 'in_treatment')
                ->where('room_id', $room->id)
                ->whereHas('appointment', function($q) use ($date) {
                    $q->whereDate('appointment_date', $date);
                })
                ->exists();

            if (!$hasActiveTreatment) {
                $this->warn("Room {$room->room_number}: Found stale status (marked occupied but no active treatment)");
                $room->markAvailable();
                $this->info("  ✅ Fixed: Marked as available");
                $fixed++;
            }
        }

        $this->newLine();
        if ($fixed > 0) {
            $this->info("✅ Fixed {$fixed} room(s) with stale status");
            $this->info("📋 You should now be able to call patients again");
        } else {
            $this->info("✅ All occupied rooms have active treatments - no fixes needed");
        }
    }
}
