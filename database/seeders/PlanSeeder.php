<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // Create the three plans from the CSV data
        $plans = [
            [
                'name' => 'Self-Managed tier',
                'slug' => 'self-managed',
                'stripe_plan_id' => 'price_1RgT3OJmhER0XpDiBNKUELdh',
                'billing_cycle' => 'monthly',
                'price' => 49.95,
                'currency' => 'usd',
                'trial_days' => 0,
                'description' => 'LOCAL DEV Self-Managed tier (49.95/month)',
                'features' => json_encode([
                    'Basic features',
                    'Self-managed scheduling',
                    'Client management'
                ]),
                'sort_order' => 1,
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Single Location tier',
                'slug' => 'single-location',
                'stripe_plan_id' => 'price_1RgT4HJmhER0XpDikdthr2xp',
                'billing_cycle' => 'monthly',
                'price' => 150.00,
                'currency' => 'usd',
                'trial_days' => 0,
                'description' => 'LOCAL DEV Single Location tier ($150/month)',
                'features' => json_encode([
                    'All Self-Managed features',
                    'Single location support',
                    'Advanced reporting',
                    'Staff management'
                ]),
                'sort_order' => 2,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Multi-Location tier',
                'slug' => 'multi-location',
                'stripe_plan_id' => 'price_1RgT57JmhER0XpDiySm2HC0h',
                'billing_cycle' => 'monthly',
                'price' => 295.00,
                'currency' => 'usd',
                'trial_days' => 0,
                'description' => 'LOCAL DEV Multi-Location tier ($295/month)',
                'features' => json_encode([
                    'All Single Location features',
                    'Multi-location support',
                    'Enterprise reporting',
                    'Priority support'
                ]),
                'sort_order' => 3,
                'is_active' => true,
                'is_featured' => false,
            ],
        /*    [
                'name' => 'Self-Managed tier',
                'slug' => 'self-managed',
                'stripe_plan_id' => 'price_1RfpQbJmhER0XpDiOsfRwv4X',
                'billing_cycle' => 'monthly',
                'price' => 49.95,
                'currency' => 'usd',
                'trial_days' => 0,
                'description' => 'Self-Managed tier (49.95/month)',
                'features' => json_encode([
                    'Basic features',
                    'Self-managed scheduling',
                    'Client management'
                ]),
                'sort_order' => 1,
                'is_active' => true,
                'is_featured' => false,
            ],
            [
                'name' => 'Single Location tier',
                'slug' => 'single-location',
                'stripe_plan_id' => 'price_1RfpRkJmhER0XpDi4cUCBw0O',
                'billing_cycle' => 'monthly',
                'price' => 150.00,
                'currency' => 'usd',
                'trial_days' => 0,
                'description' => 'Single Location tier ($150/month)',
                'features' => json_encode([
                    'All Self-Managed features',
                    'Single location support',
                    'Advanced reporting',
                    'Staff management'
                ]),
                'sort_order' => 2,
                'is_active' => true,
                'is_featured' => true,
            ],
            [
                'name' => 'Multi-Location tier',
                'slug' => 'multi-location',
                'stripe_plan_id' => 'price_1RfpS9JmhER0XpDi5rrviIO5',
                'billing_cycle' => 'monthly',
                'price' => 295.00,
                'currency' => 'usd',
                'trial_days' => 0,
                'description' => 'Multi-Location tier ($295/month)',
                'features' => json_encode([
                    'All Single Location features',
                    'Multi-location support',
                    'Enterprise reporting',
                    'Priority support'
                ]),
                'sort_order' => 3,
                'is_active' => true,
                'is_featured' => false,
            ],
            */

        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}
