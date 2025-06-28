<?php

namespace App\Console\Commands;

use App\Models\StaffPerformanceMetric;
use App\Services\CommissionService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateCommissionPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'commissions:generate-payments 
                            {--start-date= : Start date (Y-m-d)} 
                            {--end-date= : End date (Y-m-d)} 
                            {--staff= : Comma-separated list of staff IDs} 
                            {--dry-run : Run without creating payments} 
                            {--force : Force regeneration of existing payments}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate commission payments for staff based on performance metrics';

    /**
     * Execute the console command.
     */
    public function handle(CommissionService $commissionService)
    {
        $startDate = $this->option('start-date') 
            ? Carbon::parse($this->option('start-date'))
            : Carbon::now()->subMonth()->startOfMonth();
            
        $endDate = $this->option('end-date')
            ? Carbon::parse($this->option('end-date'))
            : Carbon::now()->subMonth()->endOfMonth();
            
        $staffIds = $this->option('staff')
            ? explode(',', $this->option('staff'))
            : [];
            
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        
        $this->info(sprintf(
            'Generating commission payments from %s to %s',
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        ));
        
        if ($dryRun) {
            $this->warn('Running in dry-run mode. No payments will be created.');
        }
        
        // Get performance metrics for the date range
        $query = StaffPerformanceMetric::query()
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->where('total_commission', '>', 0);
            
        if (!empty($staffIds)) {
            $query->whereIn('staff_id', $staffIds);
        }
        
        $metrics = $query->get();
        
        if ($metrics->isEmpty()) {
            $this->warn('No commission metrics found for the specified criteria.');
            return Command::SUCCESS;
        }
        
        $this->info(sprintf('Found %d commission records to process', $metrics->count()));
        
        // Group metrics by staff and payment period (monthly)
        $grouped = $metrics->groupBy(['staff_id', function ($metric) {
            return $metric->date->format('Y-m');
        }]);
        
        $bar = $this->output->createProgressBar($grouped->count());
        $bar->start();
        
        $results = [];
        
        foreach ($grouped as $staffId => $periods) {
            foreach ($periods as $period => $metrics) {
                $periodDate = Carbon::parse($period . '-01');
                $periodName = $periodDate->format('F Y');
                
                $totalCommission = $metrics->sum('total_commission');
                $totalRevenue = $metrics->sum('total_revenue');
                $totalHours = $metrics->sum('booked_hours');
                
                $result = [
                    'staff_id' => $staffId,
                    'staff_name' => $metrics->first()->staff->full_name ?? 'Unknown',
                    'period' => $periodName,
                    'start_date' => $periodDate->format('Y-m-d'),
                    'end_date' => $periodDate->copy()->endOfMonth()->format('Y-m-d'),
                    'total_revenue' => $totalRevenue,
                    'total_commission' => $totalCommission,
                    'total_hours' => $totalHours,
                    'status' => 'pending',
                ];
                
                if (!$dryRun) {
                    try {
                        DB::beginTransaction();
                        
                        // Check if payment already exists for this period
                        $existingPayment = \App\Models\CommissionPayment::query()
                            ->where('staff_id', $staffId)
                            ->where('start_date', $result['start_date'])
                            ->where('end_date', $result['end_date'])
                            ->first();
                            
                        if ($existingPayment && !$force) {
                            $result['status'] = 'skipped';
                            $result['payment_id'] = $existingPayment->id;
                        } else {
                            $paymentData = [
                                'staff_id' => $staffId,
                                'period_name' => $periodName,
                                'start_date' => $result['start_date'],
                                'end_date' => $result['end_date'],
                                'amount' => $totalCommission,
                                'status' => 'pending',
                                'notes' => sprintf(
                                    'Auto-generated commission payment for %s. Revenue: $%s, Hours: %s',
                                    $periodName,
                                    number_format($totalRevenue, 2),
                                    number_format($totalHours, 2)
                                ),
                            ];
                            
                            if ($existingPayment) {
                                $existingPayment->update($paymentData);
                                $payment = $existingPayment;
                            } else {
                                $payment = \App\Models\CommissionPayment::create($paymentData);
                            }
                            
                            $result['status'] = 'created';
                            $result['payment_id'] = $payment->id;
                            
                            // Associate metrics with this payment
                            StaffPerformanceMetric::whereIn('id', $metrics->pluck('id'))
                                ->update(['commission_payment_id' => $payment->id]);
                        }
                        
                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $result['status'] = 'error';
                        $result['error'] = $e->getMessage();
                        
                        Log::error('Error generating commission payment', [
                            'staff_id' => $staffId,
                            'period' => $period,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }
                
                $results[] = $result;
                $bar->advance();
            }
        }
        
        $bar->finish();
        $this->newLine(2);
        
        // Display results
        $headers = [
            'Staff', 'Period', 'Revenue', 'Commission', 'Hours', 'Status', 'ID'
        ];
        
        $rows = collect($results)->map(function ($result) {
            return [
                $result['staff_name'],
                $result['period'],
                '$' . number_format($result['total_revenue'], 2),
                '$' . number_format($result['total_commission'], 2),
                number_format($result['total_hours'], 2),
                $result['status'],
                $result['payment_id'] ?? 'N/A',
            ];
        })->toArray();
        
        $this->table($headers, $rows);
        
        $summary = [
            'total_commission' => collect($results)->whereIn('status', ['created', 'skipped'])->sum('total_commission'),
            'total_revenue' => collect($results)->whereIn('status', ['created', 'skipped'])->sum('total_revenue'),
            'created' => collect($results)->where('status', 'created')->count(),
            'skipped' => collect($results)->where('status', 'skipped')->count(),
            'errors' => collect($results)->where('status', 'error')->count(),
        ];
        
        $this->info(sprintf(
            'Summary: $%s total commission on $%s revenue (%d created, %d skipped, %d errors)',
            number_format($summary['total_commission'], 2),
            number_format($summary['total_revenue'], 2),
            $summary['created'],
            $summary['skipped'],
            $summary['errors']
        ));
        
        return Command::SUCCESS;
    }
}
