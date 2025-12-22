<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        if (Schema::hasTable('queues') && $driver !== 'sqlite') {
            DB::statement('ALTER TABLE queues MODIFY queue_number INT NULL');
        }
        // SQLite doesn't support MODIFY - skip for test environment
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        
        if (Schema::hasTable('queues') && $driver !== 'sqlite') {
            DB::statement('ALTER TABLE queues MODIFY queue_number INT NOT NULL');
        }
    }
};
