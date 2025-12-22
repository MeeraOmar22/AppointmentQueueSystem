<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('operating_hours', function (Blueprint $table) {
            $table->time('start_time')->nullable()->change();
            $table->time('end_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ensure we do not revert with NULL values present
        DB::table('operating_hours')->whereNull('start_time')->update(['start_time' => '00:00:00']);
        DB::table('operating_hours')->whereNull('end_time')->update(['end_time' => '00:00:00']);

        Schema::table('operating_hours', function (Blueprint $table) {
            $table->time('start_time')->nullable(false)->change();
            $table->time('end_time')->nullable(false)->change();
        });
    }
};
