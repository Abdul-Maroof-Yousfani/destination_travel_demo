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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('title')->nullable()->after('id'); // e.g., 'US', 'GB'
            $table->string('country_code')->nullable()->after('ip'); // e.g., 'US', 'GB'
            $table->string('country_name')->nullable()->after('country_code'); // e.g., 'United States', 'United Kingdom'
            $table->string('city_code')->nullable()->after('country_name'); // e.g., '212' for New York
            $table->string('city')->nullable()->after('city_code'); // e.g., 'New York'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['title', 'country_code', 'country_name', 'city_code', 'city']);
        });
    }
};
