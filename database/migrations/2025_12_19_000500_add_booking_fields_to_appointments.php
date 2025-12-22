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
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'patient_email')) {
                $table->string('patient_email')->nullable()->after('patient_phone');
            }

            if (!Schema::hasColumn('appointments', 'booking_source')) {
                $table->enum('booking_source', ['public', 'walk-in'])->default('public')->after('status');
            }

            if (!Schema::hasColumn('appointments', 'start_at')) {
                $table->dateTime('start_at')->nullable()->after('appointment_time');
            }

            if (!Schema::hasColumn('appointments', 'end_at')) {
                $table->dateTime('end_at')->nullable()->after('start_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'patient_email')) {
                $table->dropColumn('patient_email');
            }

            if (Schema::hasColumn('appointments', 'booking_source')) {
                $table->dropColumn('booking_source');
            }

            if (Schema::hasColumn('appointments', 'start_at')) {
                $table->dropColumn('start_at');
            }

            if (Schema::hasColumn('appointments', 'end_at')) {
                $table->dropColumn('end_at');
            }
        });
    }
};
