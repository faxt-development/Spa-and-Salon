<?php

namespace App\Console\Commands;

use App\Models\Staff;
use App\Services\PerformanceMetricsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateStaffPerformanceMetrics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metrics:generate-staff-performance
                            {--date= : The date to generate metrics for (Y-m-d)}
                            {--days=1 : Number of days to generate metrics for}
                            {--staff= : Comma-separated list of staff IDs}
                            {--force : Force regeneration of existing metrics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate performance metrics for staff members';

    /**
     * Execute the console command.
     */
    public function handle(PerformanceMetricsService $metricsService)
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::yesterday();

        $days = (int) $this->option('days');
        $staffIds = $this->option('staff')
            ? explode(',', $this->option('staff'))
            : [];

        $force = $this->option('force');

        $this->info(sprintf(
            'Generating performance metrics for %d day(s) starting from %s',
            $days,
            $date->format('Y-m-d')
        ));

        $bar = $this->output->createProgressBar($days);

        for ($i = 0; $i < $days; $i++) {
            $currentDate = $date->copy()->addDays($i);
            $this->generateForDate($currentDate, $staffIds, $force);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Performance metrics generation completed.');

        return Command::SUCCESS;
    }

    /**
     * Generate metrics for a specific date
     */
    protected function generateForDate(Carbon $date, array $staffIds, bool $force = false): void
    {
        $query = Staff::query()->active();

        if (!empty($staffIds)) {
            $query->whereIn('id', $staffIds);
        }

        $staffMembers = $query->get();

        $this->line(sprintf(
            'Generating metrics for %d staff members for %s',
            $staffMembers->count(),
            $date->format('Y-m-d')
        ));

        $bar = $this->output->createProgressBar($staffMembers->count());

        foreach ($staffMembers as $staff) {
            try {
                // Skip if metrics already exist and we're not forcing regeneration
                if (!$force && $staff->performanceMetrics()->whereDate('metric_date', $date)->exists()) {
                    $bar->advance();
                    continue;
                }

                $staff->generatePerformanceMetrics($date);
                $bar->advance();
            } catch (\Exception $e) {
                $this->error(sprintf(
                    'Error generating metrics for staff ID %d: %s',
                    $staff->id,
                    $e->getMessage()
                ));
                Log::error('Error generating staff performance metrics', [
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
