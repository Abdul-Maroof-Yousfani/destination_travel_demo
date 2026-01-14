<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->string('session_id')->nullable()->after('id');
            $table->string('type')->default('system')->nullable()->after('session_id');
            $table->unsignedBigInteger('booking_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropColumn('session_id');
            $table->dropColumn('type');
            $table->unsignedBigInteger('booking_id')->nullable(false)->change();
        });
    }
};
