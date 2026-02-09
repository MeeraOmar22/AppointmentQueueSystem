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
        // This migration is optional and can be skipped
        // The system uses clinic_location (string) instead of clinic_id (foreign key)
        // Uncomment below if you want to add clinic_id to appointments table
        /*
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'clinic_id')) {
                $table->foreignId('clinic_id')
                    ->nullable()
                    ->after('id');
            }
        });
        */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to rollback
    }
};