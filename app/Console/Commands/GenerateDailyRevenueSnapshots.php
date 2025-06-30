<?php

namespace App\Console\Commands;

use App\Models\Location;
use App\Models\RevenueSnapshot;
use App\Services\RevenueAnalyticsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateDailyRevenueSnapshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'revenue:generate-snapshots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily revenue snapshots for all locations';

    /**
     * Execute the console command.
     */
    /**
     * The revenue analytics service instance.
     */
    protected $revenueAnalytics;

    /**
     * Create a new command instance.
     */
    public function __construct(RevenueAnalyticsService $revenueAnalytics)
    {
        parent::__construct();
        $this->revenueAnalytics = $revenueAnalytics;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $yesterday = Carbon::yesterday();
        
        // Check if snapshot already exists for yesterday
        $exists = RevenueSnapshot::whereDate('snapshot_date', $yesterday->toDateString())->exists();
        
        if ($exists) {
            $this->info('Revenue snapshot for ' . $yesterday->format('Y-m-d') . ' already exists.');
            return 0;
        }
        
        $this->info('Generating revenue snapshots for ' . $yesterday->format('Y-m-d') . '...');
        
        // Get all locations or null for all locations combined
        $locations = Location::all();
        
        // Add null for the combined total
        $locations->push(null);
        
        $bar = $this->output->createProgressBar(count($locations));
        $bar->start();
        
        $snapshotsCreated = 0;
        
        foreach ($locations as $location) {
            $locationId = $location ? $location->id : null;
            
            try {
                DB::beginTransaction();
                
                // Get the total revenue for yesterday for this location
                $revenueData = $this->revenueAnalytics->getRevenueForDate($yesterday, $locationId);
                
                // Create the snapshot
                RevenueSnapshot::create([
                    'snapshot_date' => $yesterday,
                    'amount' => $revenueData['total'],
                    'location_id' => $locationId,
                    'breakdown' => [
                        'service_revenue' => $revenueData['service_revenue'],
                        'product_revenue' => $revenueData['product_revenue'],
                        'tax' => $revenueData['tax'],
                        'tips' => $revenueData['tips'],
                        'discounts' => $revenueData['discounts'],
                        'refunds' => $revenueData['refunds'],
                    ],
                ]);
                
                $snapshotsCreated++;
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error('Error creating snapshot for location ' . ($locationId ?? 'all') . ': ' . $e->getMessage());
            }
            
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info('Successfully created ' . $snapshotsCreated . ' revenue snapshots for ' . $yesterday->format('Y-m-d'));
        
        return 0;
    }
}
