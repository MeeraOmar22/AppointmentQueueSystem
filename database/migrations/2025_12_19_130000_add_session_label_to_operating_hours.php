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
        Schema::table('operating_hours', function (Blueprint $table) {
            $table->string('session_label')->nullable()->after('day_of_week'); // Morning, Afternoon, Evening
            $table->boolean('is_closed')->default(false)->after('end_time'); // To mark if clinic is closed that day
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operating_hours', function (Blueprint $table) {
            $table->dropColumn(['session_label', 'is_closed']);
        });
    }
};
