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
        Schema::create('segments', function (Blueprint $table) {
            $table->id();
            $table->string('departure_code');
            $table->string('arrival_code');
            $table->dateTime('departure_date');
            $table->dateTime('arrival_date');
            $table->string('flight_number')->nullable();
            $table->string('flight_duration')->nullable();
            $table->enum('direction', ['outbound', 'return'])->default('outbound')->nullable();

            $table->unsignedBigInteger('flight_id');
            $table->foreign('flight_id')->references('id')->on('flights')->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('segments');
    }
};
