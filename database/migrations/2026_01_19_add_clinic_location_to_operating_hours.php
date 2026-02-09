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
     * Adds clinic_location column to operating_hours table
     * to support multi-clinic operating hours management
     */
    public function up(): void
    {
        Schema::table('operating_hours', function (Blueprint $table) {
            $table->string('clinic_location')->default('seremban')->after('day_of_week');
            
            // Add unique constraint to ensure one entry per day per clinic
            $table->unique(['day_of_week', 'clinic_location'])->change();
        });

        // Set existing records to default clinic location
        DB::table('operating_hours')
            ->whereNull('clinic_location')
            ->update(['clinic_location' => 'seremban']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operating_hours', function (Blueprint $table) {
            $table->dropUnique(['day_of_week', 'clinic_location']);
            $table->dropColumn('clinic_location');
        });
    }
};
