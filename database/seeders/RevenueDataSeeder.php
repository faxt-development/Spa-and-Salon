<?php

namespace Database\Seeders;

use App\Models\RevenueSnapshot;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Faker\Factory as Faker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RevenueDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Configuration
        $months = 12; // Number of months of historical data to generate
        
        // Use a default location ID if locations table doesn't exist
        $locationIds = [1];
        
        try {
            // Try to get locations if the table exists
            if (\Schema::hasTable('locations')) {
                $locationIds = DB::table('locations')->pluck('id')->toArray();
                if (empty($locationIds)) {
                    $locationIds = [1]; // Fallback to a default location ID
                }
            }
        } catch (\Exception $e) {
            $this->command->warn('Could not fetch locations, using default location ID');
            Log::warning('Could not fetch locations in RevenueDataSeeder: ' . $e->getMessage());
        }
        
        $this->command->info(sprintf(
            'Generating sample revenue data for %d locations over the last %d months',
            count($locationIds),
            $months
        ));
        
        // Calculate date range
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subMonths($months);
        
        $dateRange = CarbonPeriod::create($startDate, $endDate);
        $totalDays = count($dateRange);
        $totalSnapshots = $totalDays * count($locationIds);
        
        $this->command->info(sprintf(
            'Generating %d daily snapshots (%d days × %d locations)',
            $totalSnapshots,
            $totalDays,
            count($locationIds)
        ));
        
        // Clear existing data
        RevenueSnapshot::whereBetween('snapshot_date', [$startDate, $endDate])
            ->whereIn('location_id', $locationIds)
            ->delete();
        
        $bar = $this->command->getOutput()->createProgressBar($totalSnapshots);
        $bar->start();
        
        // Base values for each location (to create some consistency)
        $locationBases = [];
        foreach ($locationIds as $locId) {
            $locId = is_object($locId) ? $locId->id : $locId; // Handle both object and scalar
            $locationBases[$locId] = [
                'base' => $faker->numberBetween(1000, 5000),
                'variation' => $faker->numberBetween(200, 1000),
                'growth_rate' => $faker->randomFloat(2, 0.98, 1.02), // Slight growth/decline
            ];
        }
        
        $snapshots = [];
        
        foreach ($dateRange as $date) {
            $dayOfWeek = $date->dayOfWeek; // 0 (Sunday) to 6 (Saturday)
            $isWeekend = $dayOfWeek === 0 || $dayOfWeek === 6;
            $isHoliday = $this->isHoliday($date);
            
            foreach ($locationIds as $locId) {
                $locationId = is_object($locId) ? $locId->id : $locId; // Handle both object and scalar
                $locationName = 'Location ' . $locationId;
                $base = $locationBases[$locationId];
                
                // Calculate base amount with growth over time
                $daysSinceStart = $startDate->diffInDays($date);
                $growthFactor = pow($base['growth_rate'], $daysSinceStart / 30); // Monthly growth
                $baseAmount = $base['base'] * $growthFactor;
                
                // Daily variation
                $variation = $base['variation'] * $faker->randomFloat(1, 0.5, 1.5);
                
                // Weekend/holiday adjustments
                $weekendMultiplier = $isWeekend ? 1.3 : 1.0; // 30% more on weekends
                $holidayMultiplier = $isHoliday ? 1.5 : 1.0; // 50% more on holidays
                
                // Seasonal variation (more in summer, less in winter)
                $month = (int) $date->format('n');
                $seasonalFactor = 1.0 + (0.3 * sin(($month - 7) * M_PI / 6)); // Peaks in July, troughs in January
                
                // Calculate total revenue
                $total = $baseAmount * $weekendMultiplier * $holidayMultiplier * $seasonalFactor + $variation;
                
                // Create breakdown
                $serviceRevenue = $total * $faker->randomFloat(2, 0.6, 0.8); // 60-80% from services
                $productRevenue = $total - $serviceRevenue;
                $tax = $total * 0.08; // 8% tax
                $tips = $serviceRevenue * $faker->randomFloat(2, 0.12, 0.20); // 12-20% tips on services
                $discounts = $total * $faker->randomFloat(2, 0.02, 0.08); // 2-8% discounts
                $refunds = $total * $faker->randomFloat(2, 0.01, 0.03); // 1-3% refunds
                
                $snapshots[] = [
                    'snapshot_date' => $date->toDateString(),
                    'amount' => $total,
                    'location_id' => $locationId,
                    'breakdown' => json_encode([
                        'service_revenue' => $serviceRevenue,
                        'product_revenue' => $productRevenue,
                        'tax' => $tax,
                        'tips' => $tips,
                        'discounts' => $discounts,
                        'refunds' => $refunds,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                // Insert in chunks to be more efficient
                if (count($snapshots) >= 1000) {
                    RevenueSnapshot::insert($snapshots);
                    $snapshots = [];
                }
                
                $bar->advance();
            }
        }
        
        // Insert any remaining snapshots
        if (!empty($snapshots)) {
            RevenueSnapshot::insert($snapshots);
        }
        
        $bar->finish();
        $this->command->newLine(2);
        $this->command->info('Successfully generated sample revenue data.');
    }
    

    
    /**
     * Check if a date is a holiday.
     *
     * @param  \Carbon\Carbon  $date
     * @return bool
     */
    protected function isHoliday(Carbon $date): bool
    {
        // Common US holidays
        $holidays = [
            '01-01', // New Year's Day
            '07-04', // Independence Day
            '12-25', // Christmas Day
            '12-31', // New Year's Eve
            // Add more holidays as needed
        ];
        
        // Check if the month-day combination matches any holiday
        return in_array($date->format('m-d'), $holidays);
    }
}
