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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number'); // e.g., "Room 1", "Room 2"
            $table->integer('capacity')->default(1);
            $table->enum('status', ['available', 'occupied'])->default('available');
            $table->string('clinic_location')->default('seremban'); // seremban or kuala_pilah
            $table->timestamps();
            $table->unique(['room_number', 'clinic_location']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
