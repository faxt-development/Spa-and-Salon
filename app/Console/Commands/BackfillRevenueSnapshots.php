<?php

namespace App\Console\Commands;

use App\Models\Location;
use App\Models\RevenueSnapshot;
use App\Services\RevenueAnalyticsService;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillRevenueSnapshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'revenue:backfill-snapshots {--start-date= : Start date (YYYY-MM-DD)} {--end-date= : End date (YYYY-MM-DD, defaults to yesterday)} {--force : Overwrite existing snapshots}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill historical revenue snapshots for a date range';

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
        // Parse dates
        $endDate = $this->option('end-date') 
            ? Carbon::parse($this->option('end-date'))
            : Carbon::yesterday();
            
        $startDate = $this->option('start-date')
            ? Carbon::parse($this->option('start-date'))
            : $endDate->copy()->subMonth();
            
        // Validate date range
        if ($startDate->isAfter($endDate)) {
            $this->error('Start date must be before or equal to end date');
            return 1;
        }
        
        $this->info(sprintf(
            'Generating revenue snapshots from %s to %s',
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        ));
        
        // Get all locations or null for all locations combined
        $locations = Location::all();
        $locations->push(null); // Add null for combined total
        
        $dateRange = CarbonPeriod::create($startDate, $endDate);
        $totalSnapshots = count($dateRange) * $locations->count();
        
        $this->info(sprintf(
            'Will generate up to %d snapshots (%d days × %d locations)',
            $totalSnapshots,
            count($dateRange),
            $locations->count()
        ));
        
        if (!$this->confirm('Do you wish to continue?', true)) {
            $this->info('Aborted');
            return 0;
        }
        
        $bar = $this->output->createProgressBar($totalSnapshots);
        $bar->start();
        
        $snapshotsCreated = 0;
        $snapshotsSkipped = 0;
        $errors = [];
        
        foreach ($dateRange as $date) {
            foreach ($locations as $location) {
                $locationId = $location ? $location->id : null;
                $locationName = $location ? $location->name : 'All Locations';
                
                // Check if snapshot already exists
                $exists = RevenueSnapshot::whereDate('snapshot_date', $date->toDateString())
                    ->where('location_id', $locationId)
                    ->exists();
                    
                if ($exists && !$this->option('force')) {
                    $snapshotsSkipped++;
                    $bar->advance();
                    continue;
                }
                
                try {
                    DB::beginTransaction();
                    
                    // Delete existing snapshot if force is enabled
                    if ($exists && $this->option('force')) {
                        RevenueSnapshot::whereDate('snapshot_date', $date->toDateString())
                            ->where('location_id', $locationId)
                            ->delete();
                    }
                    
                    // Get the total revenue for this date and location
                    $revenueData = $this->revenueAnalytics->getRevenueForDate($date, $locationId);
                    
                    // Create the snapshot
                    RevenueSnapshot::create([
                        'snapshot_date' => $date,
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
                    $errors[] = sprintf(
                        'Error creating snapshot for %s at %s: %s',
                        $locationName,
                        $date->format('Y-m-d'),
                        $e->getMessage()
                    );
                }
                
                $bar->advance();
            }
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Display summary
        $this->info(sprintf(
            'Successfully created %d revenue snapshots (skipped %d existing)',
            $snapshotsCreated,
            $snapshotsSkipped
        ));
        
        // Display any errors
        if (!empty($errors)) {
            $this->newLine();
            $this->error(sprintf('Encountered %d errors:', count($errors)));
            
            foreach ($errors as $error) {
                $this->line("- $error");
            }
        }
        
        return 0;
    }
}
