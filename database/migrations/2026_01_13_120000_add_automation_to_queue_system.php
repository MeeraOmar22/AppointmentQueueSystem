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
        // Add 'called' status to queue_status (for MySQL only)
        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('queues', function (Blueprint $table) {
                // Modify ENUM to include 'called' status
                $table->enum('queue_status', ['waiting', 'checked_in', 'called', 'in_treatment', 'completed'])
                      ->change();
            });
        }

        // Add room_id to queue table
        Schema::table('queues', function (Blueprint $table) {
            $table->unsignedBigInteger('treatment_room_id')->nullable()->after('queue_status');
        });

        // Create queue_settings table for pause/resume control
        Schema::create('queue_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_paused')->default(false);
            $table->integer('auto_transition_seconds')->default(30); // Auto-transition to in_treatment after called
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('resumed_at')->nullable();
            $table->timestamps();
        });

        // Insert default settings
        DB::table('queue_settings')->insert([
            'is_paused' => false,
            'auto_transition_seconds' => 30,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create treatment_rooms table if not exists
        if (!Schema::hasTable('treatment_rooms')) {
            Schema::create('treatment_rooms', function (Blueprint $table) {
                $table->id();
                $table->string('room_name');
                $table->string('room_code')->unique(); // e.g., A1, A2, B1
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Insert sample rooms
            DB::table('treatment_rooms')->insert([
                ['room_name' => 'Treatment Room 1', 'room_code' => 'Room 1', 'created_at' => now(), 'updated_at' => now()],
                ['room_name' => 'Treatment Room 2', 'room_code' => 'Room 2', 'created_at' => now(), 'updated_at' => now()],
                ['room_name' => 'Treatment Room 3', 'room_code' => 'Room 3', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop treatment_rooms if created
        Schema::dropIfExists('treatment_rooms');

        // Drop queue_settings
        Schema::dropIfExists('queue_settings');

        // Revert ENUM
        Schema::table('queues', function (Blueprint $table) {
            $table->enum('queue_status', ['waiting', 'checked_in', 'in_treatment', 'completed'])
                  ->change();
        });

        // Drop room_id column
        Schema::table('queues', function (Blueprint $table) {
            $table->dropColumn('treatment_room_id');
        });
    }
};
