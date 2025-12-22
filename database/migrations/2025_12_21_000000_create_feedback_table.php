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
        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->string('patient_name');
            $table->string('patient_phone');
            $table->integer('rating')->comment('1-5 stars');
            $table->text('comments')->nullable();
            $table->string('service_quality')->nullable()->comment('excellent, good, fair, poor');
            $table->string('staff_friendliness')->nullable()->comment('excellent, good, fair, poor');
            $table->string('cleanliness')->nullable()->comment('excellent, good, fair, poor');
            $table->boolean('would_recommend')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
