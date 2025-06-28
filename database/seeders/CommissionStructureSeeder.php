<?php

namespace Database\Seeders;

use App\Models\CommissionRule;
use App\Models\CommissionStructure;
use Illuminate\Database\Seeder;

class CommissionStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Basic commission structure
        $basic = CommissionStructure::create([
            'name' => 'Basic Commission',
            'description' => 'Standard commission structure for most staff',
            'type' => 'percentage',
            'default_rate' => 10.00, // 10%
            'is_active' => true,
        ]);

        // Tiered commission structure
        $tiered = CommissionStructure::create([
            'name' => 'Tiered Commission',
            'description' => 'Tiered commission based on sales volume',
            'type' => 'tiered',
            'default_rate' => 5.00, // 5% default
            'is_active' => true,
        ]);

        // Add tiered commission rules
        CommissionRule::create([
            'commission_structure_id' => $tiered->id,
            'name' => 'Tier 1: $0 - $1,000',
            'description' => '5% for sales up to $1,000',
            'condition_type' => 'sales_volume',
            'min_value' => 0,
            'max_value' => 1000,
            'rate' => 5.00,
            'priority' => 1,
            'is_active' => true,
        ]);

        CommissionRule::create([
            'commission_structure_id' => $tiered->id,
            'name' => 'Tier 2: $1,001 - $5,000',
            'description' => '7.5% for sales between $1,001 and $5,000',
            'condition_type' => 'sales_volume',
            'min_value' => 1001,
            'max_value' => 5000,
            'rate' => 7.50,
            'priority' => 2,
            'is_active' => true,
        ]);

        CommissionRule::create([
            'commission_structure_id' => $tiered->id,
            'name' => 'Tier 3: $5,001+',
            'description' => '10% for sales over $5,000',
            'condition_type' => 'sales_volume',
            'min_value' => 5001,
            'max_value' => null, // No upper limit
            'rate' => 10.00,
            'priority' => 3,
            'is_active' => true,
        ]);

        // Product-specific commission structure
        $productStructure = CommissionStructure::create([
            'name' => 'Product Specialist',
            'description' => 'Higher commissions for product sales',
            'type' => 'percentage',
            'default_rate' => 15.00, // 15% for products
            'is_active' => true,
        ]);

        // Service-specific commission structure
        $serviceStructure = CommissionStructure::create([
            'name' => 'Service Specialist',
            'description' => 'Standard service commissions with bonuses',
            'type' => 'percentage',
            'default_rate' => 12.00, // 12% for services
            'is_active' => true,
        ]);

        $this->command->info('Commission structures and rules seeded successfully!');
    }
}
