<?php

// php artisan db:seed --class=AdminAgentSeeder
namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminAgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::findOrCreate('admin');
        Role::findOrCreate('agent');

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@travelandtour.com',
            'password' => Hash::make('admin@123'),
        ]);
        $admin->assignRole('admin');

        $agent = User::create([
            'name' => 'Agent User',
            'email' => 'agent@travelandtour.com',
            'password' => Hash::make('agent@123'),
        ]);
        $agent->assignRole('agent');
    }
}
