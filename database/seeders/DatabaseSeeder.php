<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // First, seed the plans since other seeders might depend on them
        $this->call([PlanSeeder::class]);

        // Then seed other data
        $this->call([
            // Theme seeder must run before company seeder
            ThemeSeeder::class,
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
        ]);

        // Create or get admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'onboarding_completed' => true, // Mark onboarding as completed
            ]
        );
        
        // Ensure admin role is assigned
        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
        
        // Ensure admin is associated with a company by running the TestCompanySeeder
        $this->call([
            TestCompanySeeder::class,
        ]);
        
        // Update the test company to be owned by our admin user
        $company = \App\Models\Company::where('domain', 'test-spa.localhost')->first();
        if ($company) {
            $company->update(['user_id' => $admin->id]);
        }

        // Ensure admin has an active subscription to the first plan
        if ($admin->subscriptions()->doesntExist()) {
            $plan = \App\Models\Plan::orderBy('sort_order')->first();

            // Create the subscription
            $admin->subscriptions()->create([
                'plan_id' => $plan->id,
                'name' => $plan->name,
                'stripe_id' => 'seeded_subscription_' . Str::random(10),
                'stripe_status' => 'active',
                'stripe_price' => $plan->stripe_plan_id,
                'quantity' => 1,
                'trial_ends_at' => null,
                'ends_at' => now()->addYear(),
                'status' => 'active',
                'billing_cycle' => $plan->billing_cycle,
                'next_billing_date' => now()->addMonth(),
            ]);
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
