<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('segments', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->nullable()->after('flight_duration');
            $table->string('price_code')->nullable()->after('price');
        });

        // Change direction column from ENUM to VARCHAR
        DB::statement("ALTER TABLE `segments` MODIFY `direction` VARCHAR(255) NULL");

        // Add type column to bookings table
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('type')->nullable()->after('is_oneway');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('segments', function (Blueprint $table) {
            $table->dropColumn(['price', 'price_code']);
        });

        // Revert direction column back to ENUM
        DB::statement("ALTER TABLE `segments` MODIFY `direction` ENUM('outbound', 'return') NULL DEFAULT 'outbound'");

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
