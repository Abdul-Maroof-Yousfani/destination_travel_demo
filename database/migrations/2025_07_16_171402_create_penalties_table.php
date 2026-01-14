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
        Schema::create('penalties', function (Blueprint $table) {
            $table->id();
            $table->string('arrival');
            $table->string('destination');
            $table->json('cancel_fee');
            $table->json('change_fee');
            $table->json('refund_fee');
            $table->string('cabin_type')->nullable();
            $table->unsignedBigInteger('booking_item_id')->nullable();
            $table->foreign('booking_item_id')->references('id')->on('booking_items')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penalties');
    }
};
