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
        // Store the booking information when the user is view cards payment tab :)
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('order_id')->nullable(); // external reference ID
            $table->string('order_owner')->nullable(); // external reference owner like: EK, QR, etc.
            $table->string('flight_booking_id')->nullable();
            $table->string('price_code')->nullable(); // e.g., USD, EUR
            $table->string('price')->nullable(); // base price
            $table->string('tax')->nullable(); // tax
            $table->string('tax_code')->nullable(); // tax
            $table->dateTime('ticket_limit')->nullable(); // ticketing deadline
            $table->dateTime('payment_limit')->nullable(); // payment deadline
            $table->string('status')->default('initial');
            $table->boolean('is_oneway')->default(true); // like: direct, return etc.
            $table->boolean('only_search')->default(true); // if user only searched for flights
            $table->string('airline');
            $table->string('airline_id')->nullable();
            $table->string('transaction_id')->nullable(); // for payment gateway
            $table->json('passenger_details')->nullable();

            $table->unsignedBigInteger('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');

            $table->unsignedBigInteger('agent_id')->nullable();
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
