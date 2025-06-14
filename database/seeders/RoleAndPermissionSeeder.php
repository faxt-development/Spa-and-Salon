<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            'view appointments',
            'create appointments',
            'edit appointments',
            'delete appointments',
            'view clients',
            'create clients',
            'edit clients',
            'delete clients',
            'view services',
            'create services',
            'edit services',
            'delete services',
            'view staff',
            'create staff',
            'edit staff',
            'delete staff',
            'manage settings',
            'view reports',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $staffRole = Role::create(['name' => 'staff']);
        $staffPermissions = [
            'view appointments', 'create appointments', 'edit appointments',
            'view clients', 'create clients', 'edit clients',
            'view services',
        ];
        $staffRole->givePermissionTo($staffPermissions);

        $clientRole = Role::create(['name' => 'client']);
        $clientPermissions = [
            'view appointments', 'create appointments', 'edit appointments',
            'view services',
        ];
        $clientRole->givePermissionTo($clientPermissions);
    }
}
