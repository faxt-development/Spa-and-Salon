<?php

namespace App\Console\Commands;

use App\Models\RevenueSnapshot;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanupOldRevenueSnapshots extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'revenue:cleanup-snapshots 
                            {--days=365 : Delete snapshots older than this many days} 
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old revenue snapshots to save database space';

    /**
     * Execute the console command.
     */
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $cutoffDate = Carbon::now()->subDays($days);
        $isDryRun = $this->option('dry-run');
        
        $this->info(sprintf(
            'Finding revenue snapshots older than %s (%d days)',
            $cutoffDate->format('Y-m-d'),
            $days
        ));
        
        $query = RevenueSnapshot::where('snapshot_date', '<', $cutoffDate);
        $count = $query->count();
        
        if ($count === 0) {
            $this->info('No old revenue snapshots found to clean up.');
            return 0;
        }
        
        $this->info(sprintf('Found %d snapshots to delete', $count));
        
        if ($isDryRun) {
            $this->info('Dry run: No changes will be made.');
            return 0;
        }
        
        if (!$this->confirm(sprintf(
            'Are you sure you want to delete %d revenue snapshots older than %s?',
            $count,
            $cutoffDate->format('Y-m-d')
        ))) {
            $this->info('Cleanup cancelled.');
            return 0;
        }
        
        $this->info('Deleting snapshots...');
        
        $bar = $this->output->createProgressBar($count);
        $bar->start();
        
        // Delete in chunks to avoid memory issues
        $query->chunk(1000, function ($snapshots) use ($bar) {
            foreach ($snapshots as $snapshot) {
                $snapshot->delete();
                $bar->advance();
            }
        });
        
        $bar->finish();
        $this->newLine(2);
        
        $this->info(sprintf('Successfully deleted %d old revenue snapshots.', $count));
        
        // Suggest keeping at least 2 years of data for year-over-year comparisons
        $suggestedDays = 730; // ~2 years
        if ($days < $suggestedDays) {
            $this->warn(sprintf(
                'Note: For best results with year-over-year comparisons, keep at least %d days of data (currently set to %d).',
                $suggestedDays,
                $days
            ));
        }
        
        return 0;
    }
}
