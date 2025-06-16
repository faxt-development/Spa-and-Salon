<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles and permissions
        $this->call([
            RoleAndPermissionSeeder::class,
            ServiceSeeder::class,
        ]);

        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->assignRole('admin');

        // Create staff user
        $staff = User::factory()->create([
            'name' => 'Staff Member',
            'email' => 'staff@example.com',
            'password' => Hash::make('password'),
        ]);
        $staff->assignRole('staff');

        // Create client user
        $client = User::factory()->create([
            'name' => 'Client User',
            'email' => 'client@example.com',
            'password' => Hash::make('password'),
        ]);
        $client->assignRole('client');
    }
}
