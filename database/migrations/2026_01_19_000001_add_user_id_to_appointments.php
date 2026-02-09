<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * CRITICAL FIX: Add user_id foreign key to ensure patient data isolation
     * Previously: Appointments filtered by email/phone (can leak to other patients)
     * Now: Appointments tied to authenticated user ID (secure)
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Add user_id column after patient_phone for data integrity
            $table->unsignedBigInteger('user_id')->nullable()->after('patient_phone');
            
            // Create foreign key relationship
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            
            // Add index for faster queries
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
