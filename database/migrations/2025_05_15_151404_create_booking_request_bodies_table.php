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
        Schema::create('booking_request_bodies', function (Blueprint $table) {
            $table->id();
            $table->string('airline');
            $table->dateTime('ticket_limit')->nullable();
            $table->dateTime('payment_limit')->nullable();
            $table->longText('xml_body'); // for storing large XML strings
            $table->enum('status', ['original', 'change', 'expire'])->default('original'); // dont use in its not easy to change
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('booking_id');

            // Foreign keys
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
        Schema::dropIfExists('booking_request_bodies');
    }
};
