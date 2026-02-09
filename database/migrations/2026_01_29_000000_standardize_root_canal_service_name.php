<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Standardize "Root Canal Treatment" to "Root Canal"
        DB::table('services')
            ->where('name', 'Root Canal Treatment')
            ->update(['name' => 'Root Canal']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to "Root Canal Treatment" if needed
        DB::table('services')
            ->where('name', 'Root Canal')
            ->where('description', 'like', '%root canal%')
            ->update(['name' => 'Root Canal Treatment']);
    }
};
