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
        // Add unique constraint to prevent duplicate rooms for the same clinic
        // This enforces that each clinic can have only one record per room number
        Schema::table('rooms', function (Blueprint $table) {
            $table->unique(['room_number', 'clinic_location'], 'unique_room_per_clinic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropUnique('unique_room_per_clinic');
        });
    }
};
