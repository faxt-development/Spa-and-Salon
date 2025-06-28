<?php

namespace App\Services;

use App\Models\Staff;
use App\Models\StaffPerformanceMetric;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PerformanceMetricsService
{
    /**
     * Get staff performance metrics for a date range
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $period
     * @param array $filters
     * @return Collection
     */
    public function getStaffPerformanceMetrics(
        Carbon $startDate, 
        Carbon $endDate, 
        string $period = 'day',
        array $filters = []
    ): Collection {
        $dateFormat = $this->getDateFormatForPeriod($period);
        
        $query = DB::table('staff_performance_metrics as spm')
            ->join('staff', 'spm.staff_id', '=', 'staff.id')
            ->select([
                DB::raw("DATE_FORMAT(spm.metric_date, '{$dateFormat}') as period"),
                'spm.staff_id',
                DB::raw('CONCAT(staff.first_name, " ", staff.last_name) as staff_name'),
                DB::raw('SUM(spm.available_hours) as available_hours'),
                DB::raw('SUM(spm.booked_hours) as booked_hours'),
                DB::raw('AVG(spm.utilization_rate) as utilization_rate'),
                DB::raw('SUM(spm.total_revenue) as total_revenue'),
                DB::raw('AVG(spm.revenue_per_hour) as revenue_per_hour'),
                DB::raw('SUM(spm.appointments_completed) as appointments_completed'),
                DB::raw('AVG(spm.average_ticket_value) as average_ticket_value'),
                DB::raw('SUM(spm.total_commission) as total_commission'),
                DB::raw('AVG(spm.average_commission_rate) as average_commission_rate')
            ])
            ->whereBetween('spm.metric_date', [$startDate, $endDate])
            ->groupBy('period', 'spm.staff_id', 'staff_name')
            ->orderBy('period')
            ->orderBy('total_revenue', 'desc');

        // Apply filters
        if (!empty($filters['staff_id'])) {
            $query->where('spm.staff_id', $filters['staff_id']);
        }
        
        if (!empty($filters['location_id'])) {
            $query->where('staff.location_id', $filters['location_id']);
        }

        return $query->get()
            ->groupBy('period')
            ->map(function ($items) {
                return $items->take(10);
            });
    }

    /**
     * Generate performance metrics for all staff for a specific date
     * 
     * @param Carbon $date
     * @return void
     */
    public function generateDailyMetrics(\DateTimeInterface $date): void
    {
        $staffMembers = Staff::active()->get();
        
        foreach ($staffMembers as $staff) {
            $staff->generatePerformanceMetrics($date);
        }
    }

    /**
     * Generate performance metrics for a date range
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return void
     */
    public function generateMetricsForDateRange(Carbon $startDate, Carbon $endDate): void
    {
        $period = CarbonPeriod::create($startDate, $endDate);
        
        foreach ($period as $date) {
            $this->generateDailyMetrics($date);
        }
    }

    /**
     * Get staff utilization report
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $filters
     * @return Collection
     */
    public function getUtilizationReport(Carbon $startDate, Carbon $endDate, array $filters = []): Collection
    {
        $query = StaffPerformanceMetric::query()
            ->select([
                'staff_id',
                DB::raw('CONCAT(staff.first_name, " ", staff.last_name) as staff_name'),
                DB::raw('SUM(available_hours) as available_hours'),
                DB::raw('SUM(booked_hours) as booked_hours'),
                DB::raw('(SUM(booked_hours) / NULLIF(SUM(available_hours), 0)) * 100 as utilization_rate'),
                DB::raw('SUM(total_revenue) as total_revenue'),
                DB::raw('(SUM(total_revenue) / NULLIF(SUM(booked_hours), 0)) as revenue_per_hour')
            ])
            ->join('staff', 'staff_performance_metrics.staff_id', '=', 'staff.id')
            ->whereBetween('metric_date', [$startDate, $endDate])
            ->groupBy('staff_id', 'staff_name')
            ->orderBy('utilization_rate', 'desc');

        // Apply filters
        if (!empty($filters['staff_id'])) {
            $query->where('staff_id', $filters['staff_id']);
        }
        
        if (!empty($filters['location_id'])) {
            $query->where('staff.location_id', $filters['location_id']);
        }

        return $query->get();
    }

    /**
     * Get revenue per staff report
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $filters
     * @return Collection
     */
    public function getRevenuePerStaffReport(Carbon $startDate, Carbon $endDate, array $filters = []): Collection
    {
        $query = StaffPerformanceMetric::query()
            ->select([
                'staff_id',
                DB::raw('CONCAT(staff.first_name, " ", staff.last_name) as staff_name'),
                DB::raw('SUM(total_revenue) as total_revenue'),
                DB::raw('SUM(booked_hours) as total_hours'),
                DB::raw('(SUM(total_revenue) / NULLIF(SUM(booked_hours), 0)) as revenue_per_hour'),
                DB::raw('AVG(utilization_rate) as avg_utilization'),
                DB::raw('SUM(appointments_completed) as total_appointments'),
                DB::raw('(SUM(total_revenue) / NULLIF(SUM(appointments_completed), 0)) as avg_ticket_value')
            ])
            ->join('staff', 'staff_performance_metrics.staff_id', '=', 'staff.id')
            ->whereBetween('metric_date', [$startDate, $endDate])
            ->groupBy('staff_id', 'staff_name')
            ->orderBy('total_revenue', 'desc');

        // Apply filters
        if (!empty($filters['staff_id'])) {
            $query->where('staff_id', $filters['staff_id']);
        }
        
        if (!empty($filters['location_id'])) {
            $query->where('staff.location_id', $filters['location_id']);
        }

        return $query->get();
    }

    /**
     * Get the appropriate date format for the given period
     */
    protected function getDateFormatForPeriod(string $period): string
    {
        return match (strtolower($period)) {
            'hour' => '%Y-%m-%d %H:00:00',
            'day' => '%Y-%m-%d',
            'week' => '%x-W%v', // ISO week
            'month' => '%Y-%m',
            'quarter' => '%Y-Q%q',
            'year' => '%Y',
            default => '%Y-%m-%d',
        };
    }
}

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\Transaction;
use App\Models\TransactionLineItem;
use App\Models\RevenueEvent;

class PerformanceMetricsService
{
    /**
     * Get performance metrics aggregated by the specified time period
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $period daily|weekly|monthly|quarterly|yearly
     * @param array $filters Additional filters (e.g., ['staff_id' => 1, 'location_id' => 2, 'service_id' => 3])
     * @return Collection
     */
    public function getPerformanceMetrics(
        Carbon $startDate, 
        Carbon $endDate, 
        string $period = 'daily',
        array $filters = []
    ): Collection {
        // Determine the date format for grouping
        $dateFormat = $this->getDateFormatForPeriod($period);
        
        // Base query for transaction metrics
        $query = Transaction::query()
            ->select([
                DB::raw("DATE_FORMAT(transaction_date, '{$dateFormat}') as period"),
                DB::raw('COUNT(DISTINCT transactions.id) as transaction_count'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('AVG(total_amount) as avg_transaction_value'),
                DB::raw('COUNT(DISTINCT client_id) as unique_clients'),
                DB::raw('SUM(COALESCE((SELECT SUM(quantity) FROM transaction_line_items WHERE transaction_id = transactions.id AND item_type = ?), 0)) as total_items_sold'),
                DB::raw('SUM(COALESCE((SELECT SUM(amount) FROM transaction_line_items WHERE transaction_id = transactions.id AND item_type = ?), 0)) as total_tips')
            ])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->groupBy('period')
            ->orderBy('period');

        // Apply filters
        $this->applyFilters($query, $filters);

        // Get the results
        $results = $query->setBindings([
            TransactionLineItem::TYPE_PRODUCT,
            TransactionLineItem::TYPE_TIP,
        ])->get();

        // Get staff performance metrics
        $staffMetrics = $this->getStaffPerformanceMetrics($startDate, $endDate, $period, $filters);
        
        // Get service metrics
        $serviceMetrics = $this->getServicePerformanceMetrics($startDate, $endDate, $period, $filters);
        
        // Merge all metrics
        return $results->map(function ($item) use ($staffMetrics, $serviceMetrics) {
            $period = $item->period;
            
            return [
                'period' => $period,
                'transaction_count' => (int) $item->transaction_count,
                'total_revenue' => (float) $item->total_revenue,
                'avg_transaction_value' => (float) $item->avg_transaction_value,
                'unique_clients' => (int) $item->unique_clients,
                'total_items_sold' => (int) $item->total_items_sold,
                'total_tips' => (float) $item->total_tips,
                'staff_performance' => $staffMetrics->get($period, collect())->toArray(),
                'service_metrics' => $serviceMetrics->get($period, collect())->toArray(),
            ];
        });
    }

    /**
     * Get staff performance metrics
     */
    protected function getStaffPerformanceMetrics(
        Carbon $startDate, 
        Carbon $endDate, 
        string $period,
        array $filters
    ): Collection {
        $dateFormat = $this->getDateFormatForPeriod($period);
        
        $query = TransactionLineItem::query()
            ->select([
                DB::raw("DATE_FORMAT(transactions.transaction_date, '{$dateFormat}') as period"),
                'staff_id',
                DB::raw('CONCAT(staff.first_name, " ", staff.last_name) as staff_name'),
                DB::raw('COUNT(DISTINCT transactions.id) as transaction_count'),
                DB::raw('SUM(transaction_line_items.amount) as total_revenue'),
                DB::raw('AVG(transaction_line_items.amount) as avg_item_value'),
                DB::raw('SUM(transaction_line_items.commission_amount) as total_commission'),
                DB::raw('COUNT(DISTINCT transactions.client_id) as clients_served'),
                DB::raw('SUM(transaction_line_items.quantity) as items_sold'),
            ])
            ->join('transactions', 'transactions.id', '=', 'transaction_line_items.transaction_id')
            ->leftJoin('staff', 'staff.id', '=', 'transaction_line_items.staff_id')
            ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
            ->where('transactions.status', 'completed')
            ->whereIn('transaction_line_items.item_type', [
                TransactionLineItem::TYPE_SERVICE,
                TransactionLineItem::TYPE_PRODUCT,
            ])
            ->groupBy('period', 'staff_id', 'staff_name')
            ->orderBy('period')
            ->orderBy('total_revenue', 'desc');

        // Apply filters
        if (!empty($filters['staff_id'])) {
            $query->where('transaction_line_items.staff_id', $filters['staff_id']);
        }
        
        if (!empty($filters['location_id'])) {
            $query->where('transactions.location_id', $filters['location_id']);
        }

        return $query->get()
            ->groupBy('period')
            ->map(function ($items) {
                return $items->take(10); // Limit to top 10 performing staff per period
            });
    }

    /**
     * Get service performance metrics
     */
    protected function getServicePerformanceMetrics(
        Carbon $startDate, 
        Carbon $endDate, 
        string $period,
        array $filters
    ): Collection {
        $dateFormat = $this->getDateFormatForPeriod($period);
        
        $query = TransactionLineItem::query()
            ->select([
                DB::raw("DATE_FORMAT(transactions.transaction_date, '{$dateFormat}') as period"),
                'transaction_line_items.item_type',
                'transaction_line_items.name as service_name',
                DB::raw('COUNT(DISTINCT transactions.id) as times_booked'),
                DB::raw('SUM(transaction_line_items.quantity) as total_quantity'),
                DB::raw('SUM(transaction_line_items.amount) as total_revenue'),
                DB::raw('AVG(transaction_line_items.amount) as avg_sale_price'),
                DB::raw('COUNT(DISTINCT transactions.client_id) as unique_clients'),
            ])
            ->join('transactions', 'transactions.id', '=', 'transaction_line_items.transaction_id')
            ->whereBetween('transactions.transaction_date', [$startDate, $endDate])
            ->where('transactions.status', 'completed')
            ->whereIn('transaction_line_items.item_type', [
                TransactionLineItem::TYPE_SERVICE,
                TransactionLineItem::TYPE_PRODUCT,
            ])
            ->groupBy('period', 'transaction_line_items.item_type', 'transaction_line_items.name')
            ->orderBy('period')
            ->orderBy('total_revenue', 'desc');

        // Apply filters
        if (!empty($filters['service_id'])) {
            $query->where('transaction_line_items.itemable_id', $filters['service_id'])
                ->where('transaction_line_items.itemable_type', 'App\\Models\\Service');
        }
        
        if (!empty($filters['staff_id'])) {
            $query->where('transaction_line_items.staff_id', $filters['staff_id']);
        }
        
        if (!empty($filters['location_id'])) {
            $query->where('transactions.location_id', $filters['location_id']);
        }

        return $query->get()
            ->groupBy('period')
            ->map(function ($items) {
                return $items->take(10); // Limit to top 10 services per period
            });
    }

    /**
     * Apply filters to the query
     */
    protected function applyFilters($query, array $filters): void
    {
        if (!empty($filters['staff_id'])) {
            $query->where('staff_id', $filters['staff_id']);
        }
        
        if (!empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }
        
        if (!empty($filters['service_id'])) {
            $query->whereHas('lineItems', function ($q) use ($filters) {
                $q->where('itemable_type', 'App\\Models\\Service')
                  ->where('itemable_id', $filters['service_id']);
            });
        }
    }

    /**
     * Get the date format string for the given period
     */
    protected function getDateFormatForPeriod(string $period): string
    {
        return match (strtolower($period)) {
            'yearly' => '%Y',
            'quarterly' => '%Y-Q%q',
            'monthly' => '%Y-%m',
            'weekly' => '%x-W%v',
            'daily' => '%Y-%m-%d',
            default => '%Y-%m-%d',
        };
    }
}
