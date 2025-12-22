<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'visit_token')) {
                $table->string('visit_token')->nullable()->unique()->after('booking_source');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'visit_token')) {
                $table->dropUnique(['visit_token']);
                $table->dropColumn('visit_token');
            }
        });
    }
};
