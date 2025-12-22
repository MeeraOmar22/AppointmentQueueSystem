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
        Schema::create('appointments', function (Blueprint $table) {
        $table->id();
        $table->string('patient_name');
        $table->string('patient_phone');
        $table->foreignId('service_id')->constrained();
        $table->foreignId('dentist_id')->nullable()->constrained();
        $table->date('appointment_date');
        $table->time('appointment_time');
        $table->enum('status', ['booked', 'arrived', 'checked_in', 'in_queue', 'in_treatment', 'completed', 'no_show', 'cancelled', 'late'])
            ->default('booked');
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
