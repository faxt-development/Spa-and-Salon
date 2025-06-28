<?php

namespace App\Console\Commands;

use App\Services\RevenueRecognitionService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessRevenueRecognition extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'revenue:recognize 
                            {--date= : Process revenue as of this date (YYYY-MM-DD) - defaults to today}
                            {--dry-run : Run without saving any changes}
                            {--force : Force processing even if already processed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process revenue recognition for completed transactions';

    /**
     * The revenue recognition service instance.
     *
     * @var \App\Services\RevenueRecognitionService
     */
    protected $revenueService;

    /**
     * Create a new command instance.
     *
     * @param RevenueRecognitionService $revenueService
     * @return void
     */
    public function __construct(RevenueRecognitionService $revenueService)
    {
        parent::__construct();
        $this->revenueService = $revenueService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $asOfDate = $this->option('date')
            ? Carbon::parse($this->option('date'))->endOfDay()
            : now();
            
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        
        $this->info(sprintf(
            'Processing revenue recognition as of %s%s',
            $asOfDate->toDateTimeString(),
            $dryRun ? ' (dry run)' : ''
        ));
        
        try {
            if ($dryRun) {
                // In dry run mode, we'll just count the transactions that would be processed
                $query = \App\Models\Transaction::where('status', 'completed')
                    ->where('completed_at', '<=', $asOfDate);
                    
                if (!$force) {
                    $query->whereNull('revenue_recognized_at');
                }
                
                $count = $query->count();
                
                $this->info(sprintf(
                    'Would process %d transactions for revenue recognition',
                    $count
                ));
                
                if ($count > 0) {
                    $this->info('Sample of transactions that would be processed:');
                    
                    $sample = $query->take(5)->get(['id', 'transaction_number', 'completed_at', 'total_amount']);
                    $this->table(
                        ['ID', 'Transaction #', 'Completed At', 'Amount'],
                        $sample->map(function ($tx) {
                            return [
                                $tx->id,
                                $tx->transaction_number,
                                $tx->completed_at->toDateTimeString(),
                                number_format($tx->total_amount, 2)
                            ];
                        })
                    );
                }
                
                return 0;
            }
            
            // Process revenue recognition
            $results = $this->revenueService->processRevenueRecognition($asOfDate);
            
            // Output results
            $this->info(sprintf(
                'Processed %d transactions with %d revenue events recognized',
                $results['processed'],
                $results['revenue_recognized']
            ));
            
            if (!empty($results['errors'])) {
                $this->warn(sprintf('Encountered %d errors:', count($results['errors'])));
                
                foreach ($results['errors'] as $error) {
                    $this->error(sprintf(
                        'Transaction #%d: %s',
                        $error['transaction_id'],
                        $error['error']
                    ));
                }
                
                return 1;
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error processing revenue recognition: ' . $e->getMessage());
            Log::error('Revenue recognition error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return 1;
        }
    }
}
