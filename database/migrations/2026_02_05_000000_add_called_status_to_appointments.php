<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add 'called' status to appointments table enum for queue call operations
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Modify the status enum to include 'called' status
            $table->enum('status', [
                'booked',
                'confirmed',
                'cancelled',
                'no_show',
                'checked_in',
                'waiting',
                'called',
                'in_treatment',
                'completed',
                'feedback_scheduled',
                'feedback_sent'
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Remove 'called' from enum
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
            ])->change();
        });
    }
};
