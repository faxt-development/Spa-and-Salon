<?php

namespace Database\Seeders;

use App\Models\LoyaltyProgram;
use App\Models\LoyaltyTier;
use Illuminate\Database\Seeder;

class LoyaltyProgramSeeder extends Seeder
{
    public function run(): void
    {
        $program = LoyaltyProgram::create([
            'name' => 'Standard Loyalty Program',
            'description' => 'Earn points on every purchase',
            'points_per_currency' => 1.0, // 1 point per $1
            'currency_per_point' => 0.05, // $0.05 per point
            'signup_points' => 100,
            'is_active' => true,
        ]);

        // Create tiers
        $tiers = [
            [
                'name' => 'Bronze',
                'points_required' => 0,
                'multiplier' => 1.0,
                'benefits' => ['5% off services'],
                'priority' => 1,
            ],
            [
                'name' => 'Silver',
                'points_required' => 1000,
                'multiplier' => 1.1,
                'benefits' => ['10% off services', 'Priority booking'],
                'priority' => 2,
            ],
            [
                'name' => 'Gold',
                'points_required' => 5000,
                'multiplier' => 1.25,
                'benefits' => ['15% off services', 'Priority booking', 'Free gift on birthday'],
                'priority' => 3,
            ],
            [
                'name' => 'Platinum',
                'points_required' => 10000,
                'multiplier' => 1.5,
                'benefits' => ['20% off services', 'VIP booking', 'Free gift on birthday', 'Exclusive events'],
                'priority' => 4,
            ],
        ];

        foreach ($tiers as $tierData) {
            $program->tiers()->create($tierData);
        }
    }
}
