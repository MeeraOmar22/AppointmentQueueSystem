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
        // Add soft delete to dentists table
        Schema::table('dentists', function (Blueprint $table) {
            $table->softDeletes()->nullable();
        });

        // Add soft delete to services table
        Schema::table('services', function (Blueprint $table) {
            $table->softDeletes()->nullable();
        });

        // Add soft delete to users table
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dentists', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
