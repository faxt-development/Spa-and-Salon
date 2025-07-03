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
            ServiceCategorySeeder::class,
            WalkInSeeder::class,
            ServiceSeeder::class,
            StaffSeeder::class,
            AppointmentSeeder::class,
            SettingsTableSeeder::class,
            // Payment methods must be seeded before transactions
            PaymentMethodSeeder::class,
            // Room seeder must run before transactions
            RoomSeeder::class,
            // Loyalty program
            LoyaltyProgramSeeder::class,
            // New transaction architecture seeders
            TransactionSeeder::class,
            // Generate sample revenue data
            RevenueDataSeeder::class,
            // Subscription plans
            PlanSeeder::class,
        ]);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Client user will be created by the StaffSeeder if needed
        $client = User::firstOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'Client User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        if (!$client->hasRole('client')) {
            $client->assignRole('client');
        }
    }
}
