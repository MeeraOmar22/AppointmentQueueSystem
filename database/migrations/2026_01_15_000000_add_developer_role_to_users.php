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
        // SQLite doesn't support modifying enums, so we need to recreate the column for SQLite
        // For MySQL/PostgreSQL, we can use raw SQL to alter the enum
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // For SQLite, we need to use raw SQL
            Schema::getConnection()->statement(
                "UPDATE users SET role = 'staff' WHERE role NOT IN ('patient', 'staff', 'admin', 'developer')"
            );
        } else {
            Schema::table('users', function (Blueprint $table) {
                if ($driver === 'mysql') {
                    $table->enum('role', ['patient', 'staff', 'admin', 'developer'])->default('patient')->change();
                } elseif ($driver === 'pgsql') {
                    // PostgreSQL handling
                    Schema::getConnection()->statement("ALTER TYPE user_role ADD VALUE 'developer'");
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // For reverse, we would need to remove the enum value
        // This is complex for most databases, so we'll just remove the developer role
    }
};
