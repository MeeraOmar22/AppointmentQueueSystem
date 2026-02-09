<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // First, migrate existing status values to new state machine states
            // Old values to new state mapping:
            // 'pending' -> 'confirmed'
            // 'confirmed' -> 'confirmed'
            // 'arrived' -> 'checked_in'
            // 'in_queue' -> 'waiting'
            // 'in_treatment' -> 'in_treatment'
            // 'completed' -> 'completed'
            // 'cancelled' -> 'cancelled'
            // 'no_show' -> 'no_show'
            
            // Modify the status column to VARCHAR first to allow data migration
            $table->string('status')->change();
        });

        // Migrate existing data
        DB::table('appointments')->update([
            'status' => DB::raw("CASE 
                WHEN status IN ('pending', 'confirmed') THEN 'confirmed'
                WHEN status = 'arrived' THEN 'checked_in'
                WHEN status = 'in_queue' THEN 'waiting'
                WHEN status IN ('in_treatment') THEN 'in_treatment'
                WHEN status IN ('completed') THEN 'completed'
                WHEN status IN ('cancelled') THEN 'cancelled'
                WHEN status IN ('no_show') THEN 'no_show'
                WHEN status = 'late' THEN 'no_show'
                ELSE 'booked'
            END")
        ]);

        Schema::table('appointments', function (Blueprint $table) {
            // Now change to enum with new values
            $table->enum('status', [
                'booked',
                'confirmed',
                'cancelled',
                'no_show',
                'checked_in',
                'waiting',
                'in_treatment',
                'completed',
                'feedback_scheduled',
                'feedback_sent'
            ])->default('booked')->change();

            // Track when appointment was checked in
            if (!Schema::hasColumn('appointments', 'checked_in_at')) {
                $table->timestamp('checked_in_at')->nullable();
            }

            // Track when treatment started and ended
            if (!Schema::hasColumn('appointments', 'treatment_started_at')) {
                $table->timestamp('treatment_started_at')->nullable();
            }
            
            if (!Schema::hasColumn('appointments', 'treatment_ended_at')) {
                $table->timestamp('treatment_ended_at')->nullable();
            }

            // Track feedback sent time
            if (!Schema::hasColumn('appointments', 'feedback_sent_at')) {
                $table->timestamp('feedback_sent_at')->nullable();
            }

            // Index for efficient querying
            if (!Schema::hasIndex('appointments', 'appointments_status_index')) {
                $table->index('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['status']);
            if (Schema::hasColumn('appointments', 'checked_in_at')) {
                $table->dropColumn('checked_in_at');
            }
            if (Schema::hasColumn('appointments', 'treatment_started_at')) {
                $table->dropColumn('treatment_started_at');
            }
            if (Schema::hasColumn('appointments', 'treatment_ended_at')) {
                $table->dropColumn('treatment_ended_at');
            }
            if (Schema::hasColumn('appointments', 'feedback_sent_at')) {
                $table->dropColumn('feedback_sent_at');
            }
        });
    }
};
