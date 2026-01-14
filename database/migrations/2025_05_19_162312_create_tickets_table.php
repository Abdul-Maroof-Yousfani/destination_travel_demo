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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->string('airline')->nullable();
            $table->string('passenger_reference')->nullable();
            $table->string('place')->nullable();
            $table->enum('status', ['success', 'cancel'])->default('success');
            $table->string('ticket_no')->nullable();
            $table->string('type')->nullable();
            $table->dateTime('issue_date')->nullable();
            $table->string('price_code')->nullable(); // e.g., USD, EUR
            $table->string('price')->nullable(); // change precision if needed
            $table->string('price_reference')->nullable(); // change precision if needed
            $table->longText('ticket_details')->nullable(); // change precision if needed

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
