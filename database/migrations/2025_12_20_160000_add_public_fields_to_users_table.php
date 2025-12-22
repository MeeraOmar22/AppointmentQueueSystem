<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'photo')) {
                $table->string('photo')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('users', 'public_visible')) {
                $table->boolean('public_visible')->default(true)->after('photo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'public_visible')) {
                $table->dropColumn('public_visible');
            }
            if (Schema::hasColumn('users', 'photo')) {
                $table->dropColumn('photo');
            }
            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }
};
