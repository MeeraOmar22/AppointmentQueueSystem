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
        // Simply add the new columns without modifying the enum
        Schema::table('queues', function (Blueprint $table) {
            // Add room_id if it doesn't exist
            if (!Schema::hasColumn('queues', 'room_id')) {
                $table->foreignId('room_id')->nullable()->constrained();
            }

            // Add dentist_id if it doesn't exist
            if (!Schema::hasColumn('queues', 'dentist_id')) {
                $table->foreignId('dentist_id')->nullable()->constrained();
            }
        });

        // Add the appointment status fields
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'check_in_time')) {
                $table->datetime('check_in_time')->nullable();
            }

            if (!Schema::hasColumn('appointments', 'patient_email')) {
                $table->string('patient_email')->nullable();
            }

            if (!Schema::hasColumn('appointments', 'clinic_location')) {
                $table->string('clinic_location')->default('seremban');
            }

            if (!Schema::hasColumn('appointments', 'booking_source')) {
                $table->string('booking_source')->default('web');
            }

            if (!Schema::hasColumn('appointments', 'visit_token')) {
                $table->string('visit_token')->unique()->nullable();
            }

            if (!Schema::hasColumn('appointments', 'visit_code')) {
                $table->string('visit_code')->unique()->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queues', function (Blueprint $table) {
            if (Schema::hasColumn('queues', 'room_id')) {
                $table->dropForeign(['room_id']);
                $table->dropColumn('room_id');
            }

            if (Schema::hasColumn('queues', 'dentist_id')) {
                $table->dropForeign(['dentist_id']);
                $table->dropColumn('dentist_id');
            }
        });

        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'check_in_time')) {
                $table->dropColumn('check_in_time');
            }
        });
    }
};
