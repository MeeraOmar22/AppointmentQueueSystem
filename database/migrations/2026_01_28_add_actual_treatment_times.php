<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add actual treatment start and end times for tracking real duration
     * 
     * Single-clinic implementation. No clinic_location filtering needed.
     * 
     * Purpose:
     * - actual_start_time: When treatment actually began (may differ from appointment time)
     * - actual_end_time: When treatment actually ended (may differ from calculated end_at)
     * 
     * Used for:
     * - Adjusting queue ETAs based on real delays
     * - Identifying treatments that run over/under expected duration
     * - Capacity planning and resource utilization analysis
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'actual_start_time')) {
                $table->dateTime('actual_start_time')->nullable()->after('treatment_started_at');
            }

            if (!Schema::hasColumn('appointments', 'actual_end_time')) {
                $table->dateTime('actual_end_time')->nullable()->after('treatment_ended_at');
            }
        });

        // Add indexes separately to avoid hasIndexName() call
        try {
            Schema::table('appointments', function (Blueprint $table) {
                $table->index(['appointment_date', 'status'], 'idx_appointment_date_status');
            });
        } catch (\Exception $e) {
            // Index may already exist
        }

        try {
            Schema::table('appointments', function (Blueprint $table) {
                $table->index('check_in_time', 'idx_check_in_time');
            });
        } catch (\Exception $e) {
            // Index may already exist
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'actual_start_time')) {
                $table->dropColumn('actual_start_time');
            }

            if (Schema::hasColumn('appointments', 'actual_end_time')) {
                $table->dropColumn('actual_end_time');
            }

            if (Schema::hasIndex('appointments', 'idx_appointment_date_status')) {
                $table->dropIndex('idx_appointment_date_status');
            }

            if (Schema::hasIndex('appointments', 'idx_check_in_time')) {
                $table->dropIndex('idx_check_in_time');
            }
        });
    }
};
