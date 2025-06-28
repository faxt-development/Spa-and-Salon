<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AuditLogPruneCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:prune
                            {--days= : Prune entries older than this number of days}
                            {--force : Force the operation to run without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune old audit log entries';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) ($this->option('days') ?? config('audit.prune.retention_days', 365));
        
        if ($days < 1) {
            $this->error('The number of days must be at least 1.');
            return 1;
        }

        $cutoffDate = now()->subDays($days);
        
        $count = AuditLog::where('created_at', '<', $cutoffDate)->count();
        
        if ($count === 0) {
            $this->info('No audit log entries to prune.');
            return 0;
        }
        
        if (! $this->option('force') && ! $this->confirm(
            "This will delete {$count} audit log entries older than {$cutoffDate->toDateString()}. Do you wish to continue?"
        )) {
            $this->info('Pruning cancelled.');
            return 0;
        }
        
        $this->info("Pruning {$count} audit log entries older than {$cutoffDate->toDateString()}...");
        
        $deleted = AuditLog::where('created_at', '<', $cutoffDate)->delete();
        
        $this->info("Successfully pruned {$deleted} audit log entries.");
        
        return 0;
    }
}
