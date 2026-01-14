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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('airline');
            $table->string('base_price')->nullable();
            $table->string('base_price_code')->nullable();
            $table->string('tax')->nullable();
            $table->string('discount')->nullable();
            $table->string('merchant_fee')->nullable();
            $table->string('service_fee')->nullable();
            $table->enum('status', ['success', 'fail'])->default('success');
            $table->boolean('is_approve')->default(false); // admin have to approve the payment
            $table->boolean('is_refund')->default(false); // admin have to approve the payment
            $table->string('refund_status')->nullable();
            $table->string('payment_method')->nullable(); // like: credit card, cash, ezpaisa etc.
            $table->string('transaction_id')->nullable(); // of payment gateway
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('booking_id');

            // Foreign keys
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
