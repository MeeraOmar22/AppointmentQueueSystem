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
        // First, convert existing 'in_service' values to 'in_treatment'
        DB::statement("UPDATE queues SET queue_status = 'in_treatment' WHERE queue_status = 'in_service'");
        
        // SQLite doesn't support ENUM or MODIFY, so we only do the UPDATE above
        // MySQL users should handle ENUM updates manually if needed
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE queues MODIFY queue_status ENUM('waiting', 'checked_in', 'in_treatment', 'completed') DEFAULT 'waiting'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert changes
        DB::statement("UPDATE queues SET queue_status = 'waiting' WHERE queue_status = 'checked_in'");
        
        // SQLite doesn't support ENUM or MODIFY
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE queues MODIFY queue_status ENUM('waiting', 'called', 'in_service', 'in_treatment', 'completed') DEFAULT 'waiting'");
        }
    }
};
