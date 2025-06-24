<?php

namespace Database\Seeders;

use App\Models\WalkIn;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WalkInSeeder extends Seeder
{
    public function run()
    {
        // Clear existing data
        WalkIn::truncate();

        // Create sample walk-ins with different statuses
        $now = now();
        
        // Waiting walk-ins
        WalkIn::create([
            'name' => 'John Doe',
            'phone' => '555-0101',
            'party_size' => 2,
            'status' => 'waiting',
            'estimated_wait_time' => 15,
            'check_in_time' => $now->copy()->subMinutes(5),
            'created_at' => $now->copy()->subMinutes(5),
        ]);

        WalkIn::create([
            'name' => 'Jane Smith',
            'phone' => '555-0102',
            'party_size' => 1,
            'status' => 'waiting',
            'estimated_wait_time' => 30,
            'check_in_time' => $now->copy()->subMinutes(10),
            'created_at' => $now->copy()->subMinutes(10),
        ]);

        // In-service walk-in
        WalkIn::create([
            'name' => 'Bob Johnson',
            'phone' => '555-0103',
            'party_size' => 1,
            'status' => 'in_service',
            'estimated_wait_time' => 0,
            'check_in_time' => $now->copy()->subMinutes(60),
            'service_start_time' => $now->copy()->subMinutes(30),
            'created_at' => $now->copy()->subMinutes(60),
        ]);

        $this->command->info('Sample walk-ins created successfully.');
    }
}
