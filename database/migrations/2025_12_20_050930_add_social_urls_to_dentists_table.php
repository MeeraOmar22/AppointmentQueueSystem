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
            if (!Schema::hasColumn('dentists', 'twitter_url')) {
                $table->string('twitter_url')->nullable()->after('years_of_experience');
            }
            if (!Schema::hasColumn('dentists', 'facebook_url')) {
                $table->string('facebook_url')->nullable()->after('twitter_url');
            }
            if (!Schema::hasColumn('dentists', 'linkedin_url')) {
                $table->string('linkedin_url')->nullable()->after('facebook_url');
            }
            if (!Schema::hasColumn('dentists', 'instagram_url')) {
                $table->string('instagram_url')->nullable()->after('linkedin_url');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dentists', function (Blueprint $table) {
            if (Schema::hasColumn('dentists', 'twitter_url')) {
                $table->dropColumn('twitter_url');
            }
            if (Schema::hasColumn('dentists', 'facebook_url')) {
                $table->dropColumn('facebook_url');
            }
            if (Schema::hasColumn('dentists', 'linkedin_url')) {
                $table->dropColumn('linkedin_url');
            }
            if (Schema::hasColumn('dentists', 'instagram_url')) {
                $table->dropColumn('instagram_url');
            }
        });
    }
};
