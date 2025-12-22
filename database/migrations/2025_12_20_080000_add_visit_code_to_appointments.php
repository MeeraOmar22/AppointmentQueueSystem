<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'visit_code')) {
                $table->string('visit_code')->nullable()->unique()->after('visit_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'visit_code')) {
                $table->dropUnique(['visit_code']);
                $table->dropColumn('visit_code');
            }
        });
    }
};
