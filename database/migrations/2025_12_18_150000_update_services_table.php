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
        Schema::table('services', function (Blueprint $table) {
            // Rename service_name to name if it exists
            if (Schema::hasColumn('services', 'service_name')) {
                $table->renameColumn('service_name', 'name');
            }
            
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('services', 'description')) {
                $table->text('description')->nullable()->after('name');
            }
            
            if (!Schema::hasColumn('services', 'price')) {
                $table->decimal('price', 8, 2)->default(0)->after('description');
            }
            
            if (!Schema::hasColumn('services', 'status')) {
                $table->integer('status')->default(1)->after('price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Reverse the changes
            if (Schema::hasColumn('services', 'name')) {
                $table->renameColumn('name', 'service_name');
            }
            
            if (Schema::hasColumn('services', 'description')) {
                $table->dropColumn('description');
            }
            
            if (Schema::hasColumn('services', 'price')) {
                $table->dropColumn('price');
            }
            
            if (Schema::hasColumn('services', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
