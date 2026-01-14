<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\AirportSeeder;
use Database\Seeders\AdminAgentSeeder;
use Database\Seeders\RolePermissionSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AirportSeeder::class,
            AdminAgentSeeder::class,
            RolePermissionSeeder::class,
        ]);
    }
}
