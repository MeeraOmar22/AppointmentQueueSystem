<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Converts day_of_week column from integer (0-6) to string (Monday-Sunday)
     * This ensures consistency with blade templates and query filters
     */
    public function up(): void
    {
        // Map numeric days to day names
        $dayMapping = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        // Get all existing records and convert them
        $hours = DB::table('operating_hours')->get();
        
        foreach ($hours as $hour) {
            $dayName = $dayMapping[$hour->day_of_week] ?? 'Unknown';
            
            DB::table('operating_hours')
                ->where('id', $hour->id)
                ->update(['day_of_week' => $dayName]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Map day names back to numeric days
        $dayMapping = [
            'Sunday' => 0,
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6,
        ];

        // Get all existing records and convert them back
        $hours = DB::table('operating_hours')->get();
        
        foreach ($hours as $hour) {
            $dayNum = $dayMapping[$hour->day_of_week] ?? 0;
            
            DB::table('operating_hours')
                ->where('id', $hour->id)
                ->update(['day_of_week' => $dayNum]);
        }
    }
};
