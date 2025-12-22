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
        Schema::create('queues', function (Blueprint $table) {
        $table->id();
        $table->foreignId('appointment_id')->constrained();
        $table->integer('queue_number')->nullable();
        $table->enum('queue_status', ['waiting', 'called', 'in_treatment', 'completed'])
            ->default('waiting');
        $table->timestamp('check_in_time')->nullable();
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queues');
    }
};
