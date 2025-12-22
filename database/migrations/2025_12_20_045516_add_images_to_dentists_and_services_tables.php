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
        Schema::table('dentists', function (Blueprint $table) {
            if (!Schema::hasColumn('dentists', 'photo')) {
                $table->string('photo')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('dentists', 'bio')) {
                $table->text('bio')->nullable()->after('photo');
            }
            if (!Schema::hasColumn('dentists', 'years_of_experience')) {
                $table->integer('years_of_experience')->nullable()->after('bio');
            }
        });

        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'image')) {
                $table->string('image')->nullable()->after('description');
            }
            if (!Schema::hasColumn('services', 'duration_minutes')) {
                $table->integer('duration_minutes')->nullable()->after('image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dentists', function (Blueprint $table) {
            if (Schema::hasColumn('dentists', 'photo')) {
                $table->dropColumn('photo');
            }
            if (Schema::hasColumn('dentists', 'bio')) {
                $table->dropColumn('bio');
            }
            if (Schema::hasColumn('dentists', 'years_of_experience')) {
                $table->dropColumn('years_of_experience');
            }
        });

        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'image')) {
                $table->dropColumn('image');
            }
            if (Schema::hasColumn('services', 'duration_minutes')) {
                $table->dropColumn('duration_minutes');
            }
        });
    }
};
