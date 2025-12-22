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
        // Check if we're using SQLite (test environment)
        $driver = Schema::getConnection()->getDriverName();
        
        if ($driver === 'sqlite') {
            // SQLite doesn't support ALTER TABLE MODIFY or changing ENUM values
            // In tests, tables are created fresh so no update needed
            Schema::table('queues', function (Blueprint $table) {
                if (!Schema::hasColumn('queues', 'room_id')) {
                    $table->foreignId('room_id')->nullable()->constrained();
                }
                if (!Schema::hasColumn('queues', 'dentist_id')) {
                    $table->foreignId('dentist_id')->nullable()->constrained();
                }
            });
        } else {
            // MySQL/PostgreSQL can use UPDATE and change()
            DB::update("UPDATE `queues` SET `queue_status` = 'in_treatment' WHERE `queue_status` = 'in_service'");

            Schema::table('queues', function (Blueprint $table) {
                if (!Schema::hasColumn('queues', 'room_id')) {
                    $table->foreignId('room_id')->nullable()->constrained();
                }
                if (!Schema::hasColumn('queues', 'dentist_id')) {
                    $table->foreignId('dentist_id')->nullable()->constrained();
                }
                $table->enum('queue_status', ['waiting', 'called', 'in_treatment', 'completed'])
                    ->default('waiting')
                    ->change();
            });
        }
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

            // Revert queue_status enum
            $table->enum('queue_status', ['waiting', 'in_service', 'completed'])
                ->default('waiting')
                ->change();
        });
    }
};
