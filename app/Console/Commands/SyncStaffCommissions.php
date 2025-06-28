<?php

namespace App\Console\Commands;

use App\Models\Staff;
use App\Services\CommissionService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncStaffCommissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commissions:sync 
                            {--date= : The date to sync commissions for (Y-m-d)} 
                            {--days=1 : Number of days to sync commissions for} 
                            {--staff= : Comma-separated list of staff IDs} 
                            {--force : Force resync of existing commissions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync commission calculations for staff members';

    /**
     * Execute the console command.
     */
    public function handle(CommissionService $commissionService)
    {
        $date = $this->option('date') 
            ? Carbon::parse($this->option('date'))
            : Carbon::today();
            
        $days = (int) $this->option('days');
        $staffIds = $this->option('staff')
            ? explode(',', $this->option('staff'))
            : [];
            
        $force = $this->option('force');
        
        $this->info(sprintf(
            'Syncing commissions for %d day(s) starting from %s',
            $days,
            $date->format('Y-m-d')
        ));
        
        $bar = $this->output->createProgressBar($days);
        
        for ($i = 0; $i < $days; $i++) {
            $currentDate = $date->copy()->subDays($i);
            $this->syncForDate($currentDate, $staffIds, $force, $commissionService);
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('Commission sync completed.');
        
        return Command::SUCCESS;
    }
    
    /**
     * Sync commissions for a specific date
     */
    protected function syncForDate(Carbon $date, array $staffIds, bool $force, CommissionService $commissionService): void
    {
        $query = Staff::query()->active();
        
        if (!empty($staffIds)) {
            $query->whereIn('id', $staffIds);
        }
        
        $staffMembers = $query->get();
        
        $this->line(sprintf(
            'Syncing commissions for %d staff members for %s',
            $staffMembers->count(),
            $date->format('Y-m-d')
        ));
        
        $bar = $this->output->createProgressBar($staffMembers->count());
        
        foreach ($staffMembers as $staff) {
            try {
                $this->line(sprintf(
                    'Syncing commissions for staff ID %d (%s)',
                    $staff->id,
                    $staff->full_name
                ));
                
                // This would be implemented based on your specific business logic
                // to sync commissions for the given staff member and date
                $result = $commissionService->syncStaffCommissions(
                    $date->copy()->startOfDay(),
                    $date->copy()->endOfDay(),
                    $staff->id
                );
                
                $this->info(sprintf(
                    'Synced commissions for staff ID %d: %d records updated',
                    $staff->id,
                    $result['updated'] ?? 0
                ));
                
                $bar->advance();
            } catch (\Exception $e) {
                $this->error(sprintf(
                    'Error syncing commissions for staff ID %d: %s',
                    $staff->id,
                    $e->getMessage()
                ));
                Log::error('Error syncing staff commissions', [
                    'staff_id' => $staff->id,
                    'date' => $date->format('Y-m-d'),
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }
        
        $bar->finish();
        $this->newLine();
    }
}
