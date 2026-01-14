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
        Schema::create('passengers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('passenger_reference')->nullable();
            $table->string('type')->nullable();
            $table->string('given_name');
            $table->string('surname');
            $table->date('dob');
            $table->string('nationality');
            $table->string('passport_no')->nullable();
            $table->date('passport_exp')->nullable();
            $table->unsignedBigInteger('client_id');

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
        Schema::dropIfExists('passengers');
    }
};
