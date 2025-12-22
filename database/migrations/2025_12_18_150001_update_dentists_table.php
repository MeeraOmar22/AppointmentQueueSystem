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
            // Rename dentist_name to name if it exists
            if (Schema::hasColumn('dentists', 'dentist_name')) {
                $table->renameColumn('dentist_name', 'name');
            }
            
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('dentists', 'specialization')) {
                $table->string('specialization')->nullable()->after('name');
            }
            
            if (!Schema::hasColumn('dentists', 'email')) {
                $table->string('email')->nullable()->unique()->after('specialization');
            }
            
            if (!Schema::hasColumn('dentists', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dentists', function (Blueprint $table) {
            // Reverse the changes
            if (Schema::hasColumn('dentists', 'name')) {
                $table->renameColumn('name', 'dentist_name');
            }
            
            if (Schema::hasColumn('dentists', 'specialization')) {
                $table->dropColumn('specialization');
            }
            
            if (Schema::hasColumn('dentists', 'email')) {
                $table->dropColumn('email');
            }
            
            if (Schema::hasColumn('dentists', 'phone')) {
                $table->dropColumn('phone');
            }
        });
    }
};
