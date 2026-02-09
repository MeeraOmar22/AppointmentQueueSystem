<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * FEEDBACK RESPONSE TRACKING IMPLEMENTATION
     * Adds columns to track feedback request lifecycle:
     * - When request was sent
     * - When patient responded
     * - Response status (pending, responded, expired)
     * - How request was sent (email, sms, manual)
     */
    public function up(): void
    {
        // Add response tracking columns to feedback table
        Schema::table('feedback', function (Blueprint $table) {
            $table->timestamp('feedback_request_sent_at')->nullable()->after('would_recommend');
            $table->timestamp('feedback_response_received_at')->nullable()->after('feedback_request_sent_at');
            $table->boolean('is_responded')->default(false)->after('feedback_response_received_at');
            $table->enum('feedback_request_method', ['email', 'sms', 'manual'])->nullable()->after('is_responded');
        });

        // Create feedback_requests table for tracking request lifecycle
        Schema::create('feedback_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->string('patient_name');
            $table->string('patient_phone')->nullable();
            $table->string('patient_email')->nullable();
            $table->timestamp('request_sent_at')->nullable();
            $table->timestamp('response_received_at')->nullable();
            $table->enum('response_status', ['pending', 'responded', 'expired', 'not_sent'])->default('pending');
            $table->enum('sent_via', ['email', 'sms', 'manual', 'none'])->default('none');
            $table->integer('reminder_count')->default(0);
            $table->timestamp('last_reminder_sent_at')->nullable();
            $table->timestamps();
            
            // Indexes for queries
            $table->index('response_status');
            $table->index('request_sent_at');
            $table->index('appointment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->dropColumn([
                'feedback_request_sent_at',
                'feedback_response_received_at',
                'is_responded',
                'feedback_request_method',
            ]);
        });

        Schema::dropIfExists('feedback_requests');
    }
};
