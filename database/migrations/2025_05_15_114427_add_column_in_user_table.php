<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // public function up(): void
    // {
    //     Schema::table('users', function (Blueprint $table) {
    //         $table->string('role')->after('name')->default('agent'); // admin, agent
    //     });

        
    //     DB::table('users')->insert([
    //         [
    //             'name' => 'Admin User',
    //             'role' => 'admin',
    //             'email' => 'admin@travelandtour.com',
    //             'password' => Hash::make('admin@123'),
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ],
    //         [
    //             'name' => 'Agent User',
    //             'role' => 'agent',
    //             'email' => 'agent@travelandtour.com',
    //             'password' => Hash::make('agent@123'),
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ],
    //     ]);
    // }

    // public function down(): void
    // {
    //     Schema::table('users', function (Blueprint $table) {
    //         $table->dropColumn([
    //             'role',
    //         ]);
    //     });

    //     DB::table('users')->whereIn('email', [
    //         'admin@travelandtour.com',
    //         'agent@travelandtour.com',
    //     ])->delete();
    // }


};
