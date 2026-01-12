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
        Schema::table('appointments', function (Blueprint $table) {
            // Track WhatsApp message delivery
            $table->timestamp('confirmation_sent_at')->nullable();
            $table->timestamp('reminder_24h_sent_at')->nullable();
            $table->timestamp('reminder_today_sent_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn([
                'confirmation_sent_at',
                'reminder_24h_sent_at',
                'reminder_today_sent_at',
            ]);
        });
    }
};
