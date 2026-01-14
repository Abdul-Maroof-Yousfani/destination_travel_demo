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
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->string('airline');
            $table->string('departure_code');
            $table->string('arrival_code');
            $table->dateTime('departure_date');
            $table->dateTime('arrival_date')->nullable();
            $table->boolean('is_connected')->default(false);
            $table->json('pax_count')->nullable(); // e.g., {"adults": 1, "children": 0, "infant": 0}
            $table->string('cabin_class'); // e.g., Economy, Business
            $table->string('price'); // change precision if needed
            $table->string('price_code'); // e.g., USD, EUR

            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flights');
    }
};
