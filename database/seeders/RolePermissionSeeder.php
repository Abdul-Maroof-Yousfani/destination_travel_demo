<?php

// php artisan db:seed --class=RolePermissionSeeder
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $agent = Role::firstOrCreate(['name' => 'agent']);

        $permissions = [
            'booking actions',
            'delete bookings',
            'manage all bookings',
            'cancel booking',
            'manage bookings',
            'view bookings',
            'manage users',
            'view global analytics',
            'view dashboard',
            'manage agents',
            'manage setting',
            'manage roles',
            'manage payment',
            'issue tickets',
            'manage airports',
            'download logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminPermissions = collect($permissions);
        $admin->syncPermissions($adminPermissions);

        $agent->syncPermissions(['view dashboard']);
    }
}