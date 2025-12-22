<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        Schema::table('appointments', function (Blueprint $table) use ($driver) {
            if ($driver !== 'sqlite') {
                // Change status enum to include new values (not supported in SQLite)
                $table->enum('status', ['booked', 'arrived', 'in_queue', 'in_treatment', 'completed', 'no_show', 'cancelled', 'late'])
                    ->default('booked')
                    ->change();
                    
                // Make dentist_id nullable
                $table->foreignId('dentist_id')->nullable()->change();
            }

            // Add check_in_time if it doesn't exist
            if (!Schema::hasColumn('appointments', 'check_in_time')) {
                $table->datetime('check_in_time')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('status', ['booked', 'cancelled', 'completed', 'no-show'])
                ->default('booked')
                ->change();
        });
    }
};
