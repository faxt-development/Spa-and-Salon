<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\TransactionLineItem;
use App\Models\RevenueEvent;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FinancialReportingService
{
    /**
     * Get revenue summary for a given date range
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $filters Additional filters (e.g., ['location_id' => 1, 'service_id' => 2])
     * @return array
     */
    public function getRevenueSummary(Carbon $startDate, Carbon $endDate, array $filters = []): array
    {
        $query = RevenueEvent::query()
            ->whereBetween('recognized_at', [$startDate, $endDate])
            ->select([
                DB::raw('SUM(amount) as total_revenue'),
                DB::raw('COUNT(DISTINCT transaction_id) as transaction_count'),
                DB::raw('AVG(amount) as average_transaction_value'),
                DB::raw('SUM(CASE WHEN line_item_type = "service" THEN amount ELSE 0 END) as service_revenue'),
                DB::raw('SUM(CASE WHEN line_item_type = "product" THEN amount ELSE 0 END) as product_revenue'),
                DB::raw('SUM(CASE WHEN line_item_type = "membership" THEN amount ELSE 0 END) as membership_revenue'),
                DB::raw('SUM(CASE WHEN line_item_type = "package" THEN amount ELSE 0 END) as package_revenue'),
                DB::raw('SUM(CASE WHEN line_item_type = "tip" THEN amount ELSE 0 END) as tips'),
                DB::raw('SUM(CASE WHEN line_item_type = "tax" THEN amount ELSE 0 END) as taxes'),
            ]);

        // Apply filters
        $this->applyFilters($query, $filters);

        return (array) $query->first()->toArray();
    }

    /**
     * Get revenue by date period (daily, weekly, monthly, yearly)
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $period daily|weekly|monthly|yearly
     * @param array $filters
     * @return Collection
     */
    public function getRevenueByPeriod(
        Carbon $startDate, 
        Carbon $endDate, 
        string $period = 'daily',
        array $filters = []
    ): Collection {
        // Determine the date format for grouping
        $dateFormat = $this->getDateFormatForPeriod($period);
        
        $query = RevenueEvent::query()
            ->whereBetween('recognized_at', [$startDate, $endDate])
            ->select([
                DB::raw("DATE_FORMAT(recognized_at, '{$dateFormat}') as period"),
                DB::raw('SUM(amount) as revenue'),
                DB::raw('COUNT(DISTINCT transaction_id) as transaction_count'),
                DB::raw('SUM(CASE WHEN line_item_type = "service" THEN amount ELSE 0 END) as service_revenue'),
                DB::raw('SUM(CASE WHEN line_item_type = "product" THEN amount ELSE 0 END) as product_revenue'),
            ])
            ->groupBy('period')
            ->orderBy('period');

        // Apply filters
        $this->applyFilters($query, $filters);

        $results = $query->get();

        // Fill in any missing periods with zero values
        return $this->fillMissingPeriods($results, $startDate, $endDate, $period);
    }

    /**
     * Get revenue by service category
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $filters
     * @return Collection
     */
    public function getRevenueByServiceCategory(Carbon $startDate, Carbon $endDate, array $filters = []): Collection
    {
        $query = \App\Models\OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->leftJoin('service_categories', 'order_items.service_category_id', '=', 'service_categories.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNotNull('order_items.service_category_id')
            ->select([
                'service_categories.id as category_id',
                'service_categories.name as category_name',
                DB::raw('SUM(order_items.total) as revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count'),
                DB::raw('SUM(order_items.quantity) as service_count'),
                DB::raw('AVG(order_items.total) as average_service_price'),
                DB::raw('COUNT(DISTINCT orders.client_id) as client_count'),
            ])
            ->groupBy('service_categories.id', 'service_categories.name')
            ->orderBy('revenue', 'desc');
            
        // Apply filters
        $this->applyServiceCategoryFilters($query, $filters);
        
        return $query->get();
    }
    
    /**
     * Get service performance by category
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $filters
     * @return Collection
     */
    public function getServicePerformanceByCategory(Carbon $startDate, Carbon $endDate, array $filters = []): Collection
    {
        $query = \App\Models\OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('services', 'order_items.itemable_id', '=', 'services.id')
            ->leftJoin('service_categories', 'order_items.service_category_id', '=', 'service_categories.id')
            ->where('order_items.itemable_type', 'App\\Models\\Service')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNotNull('order_items.service_category_id')
            ->select([
                'services.id as service_id',
                'services.name as service_name',
                'service_categories.id as category_id',
                'service_categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as service_count'),
                DB::raw('SUM(order_items.total) as total_revenue'),
                DB::raw('AVG(order_items.unit_price) as average_price'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count'),
            ])
            ->groupBy('services.id', 'services.name', 'service_categories.id', 'service_categories.name')
            ->orderBy('service_categories.name')
            ->orderBy('total_revenue', 'desc');
            
        // Apply filters
        $this->applyServiceCategoryFilters($query, $filters);
        
        return $query->get();
    }
    
    /**
     * Get revenue by category (service, product, etc.)
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $filters
     * @return Collection
     */
    public function getRevenueByCategory(Carbon $startDate, Carbon $endDate, array $filters = []): Collection
    {
        $query = RevenueEvent::query()
            ->whereBetween('recognized_at', [$startDate, $endDate])
            ->select([
                'line_item_type as category',
                DB::raw('SUM(amount) as revenue'),
                DB::raw('COUNT(DISTINCT transaction_id) as transaction_count'),
                DB::raw('COUNT(*) as item_count'),
            ])
            ->groupBy('line_item_type')
            ->orderBy('revenue', 'desc');

        // Apply filters
        $this->applyFilters($query, $filters);

        return $query->get();
    }

    /**
     * Get revenue by staff member
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $filters
     * @return Collection
     */
    public function getRevenueByStaff(Carbon $startDate, Carbon $endDate, array $filters = []): Collection
    {
        $query = RevenueEvent::query()
            ->with('staff')
            ->whereBetween('recognized_at', [$startDate, $endDate])
            ->whereNotNull('staff_id')
            ->select([
                'staff_id',
                DB::raw('SUM(amount) as revenue'),
                DB::raw('COUNT(DISTINCT transaction_id) as transaction_count'),
                DB::raw('COUNT(*) as item_count'),
                DB::raw('SUM(CASE WHEN line_item_type = "service" THEN amount ELSE 0 END) as service_revenue'),
                DB::raw('SUM(CASE WHEN line_item_type = "product" THEN amount ELSE 0 END) as product_revenue'),
                DB::raw('SUM(commission_amount) as total_commission'),
            ])
            ->groupBy('staff_id')
            ->orderBy('revenue', 'desc');

        // Apply filters
        $this->applyFilters($query, $filters);

        return $query->get();
    }

    /**
     * Get revenue by service location
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $filters
     * @return Collection
     */
    public function getRevenueByLocation(Carbon $startDate, Carbon $endDate, array $filters = []): Collection
    {
        $query = Transaction::query()
            ->with('location')
            ->whereHas('revenueEvents')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select([
                'location_id',
                DB::raw('COUNT(DISTINCT transactions.id) as transaction_count'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('AVG(total_amount) as average_transaction_value'),
            ])
            ->groupBy('location_id')
            ->orderBy('total_revenue', 'desc');

        // Apply filters
        if (!empty($filters['service_id'])) {
            $query->whereHas('lineItems', function ($q) use ($filters) {
                $q->where('line_item_type', 'service')
                  ->where('line_item_id', $filters['service_id']);
            });
        }

        if (!empty($filters['staff_id'])) {
            $query->where('staff_id', $filters['staff_id']);
        }

        return $query->get();
    }

    /**
     * Get top performing services
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int $limit
     * @param array $filters
     * @return Collection
     */
    public function getTopServices(
        Carbon $startDate, 
        Carbon $endDate, 
        int $limit = 10,
        array $filters = []
    ): Collection {
        $query = RevenueEvent::query()
            ->with('lineItem')
            ->whereBetween('recognized_at', [$startDate, $endDate])
            ->where('line_item_type', 'service')
            ->select([
                'line_item_id',
                'line_item_type',
                DB::raw('SUM(amount) as revenue'),
                DB::raw('COUNT(DISTINCT transaction_id) as transaction_count'),
                DB::raw('COUNT(*) as item_count'),
            ])
            ->groupBy('line_item_id', 'line_item_type')
            ->orderBy('revenue', 'desc')
            ->limit($limit);

        // Apply filters
        $this->applyFilters($query, $filters);

        return $query->get();
    }

    /**
     * Get top performing products
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int $limit
     * @param array $filters
     * @return Collection
     */
    public function getTopProducts(
        Carbon $startDate, 
        Carbon $endDate, 
        int $limit = 10,
        array $filters = []
    ): Collection {
        $query = RevenueEvent::query()
            ->with('lineItem')
            ->whereBetween('recognized_at', [$startDate, $endDate])
            ->where('line_item_type', 'product')
            ->select([
                'line_item_id',
                'line_item_type',
                DB::raw('SUM(amount) as revenue'),
                DB::raw('SUM(quantity) as quantity_sold'),
                DB::raw('COUNT(DISTINCT transaction_id) as transaction_count'),
            ])
            ->groupBy('line_item_id', 'line_item_type')
            ->orderBy('revenue', 'desc')
            ->limit($limit);

        // Apply filters
        $this->applyFilters($query, $filters);

        return $query->get();
    }

    /**
     * Get customer spending statistics
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int $limit
     * @param array $filters
     * @return Collection
     */
    public function getTopCustomers(
        Carbon $startDate, 
        Carbon $endDate, 
        int $limit = 10,
        array $filters = []
    ): Collection {
        $query = Transaction::query()
            ->with('client')
            ->whereHas('revenueEvents')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select([
                'client_id',
                DB::raw('COUNT(DISTINCT transactions.id) as visit_count'),
                DB::raw('SUM(total_amount) as total_spent'),
                DB::raw('MAX(created_at) as last_visit'),
                DB::raw('AVG(total_amount) as average_spend_per_visit'),
            ])
            ->groupBy('client_id')
            ->orderBy('total_spent', 'desc')
            ->limit($limit);

        // Apply filters
        if (!empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        return $query->get();
    }

    /**
     * Get commission report for staff
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $filters
     * @return Collection
     */
    public function getCommissionReport(Carbon $startDate, Carbon $endDate, array $filters = []): Collection
    {
        $query = RevenueEvent::query()
            ->with('staff')
            ->whereBetween('recognized_at', [$startDate, $endDate])
            ->where('commission_amount', '>', 0)
            ->select([
                'staff_id',
                DB::raw('SUM(amount) as total_sales'),
                DB::raw('SUM(commission_amount) as total_commission'),
                DB::raw('COUNT(DISTINCT transaction_id) as transaction_count'),
                DB::raw('COUNT(*) as item_count'),
                DB::raw('AVG(commission_rate * 100) as average_commission_rate'),
            ])
            ->groupBy('staff_id')
            ->orderBy('total_commission', 'desc');

        // Apply filters
        if (!empty($filters['staff_id'])) {
            $query->where('staff_id', $filters['staff_id']);
        }

        if (!empty($filters['location_id'])) {
            $query->whereHas('transaction', function ($q) use ($filters) {
                $q->where('location_id', $filters['location_id']);
            });
        }

        return $query->get();
    }

    /**
     * Get sales tax report
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $filters
     * @return Collection
     */
    public function getSalesTaxReport(Carbon $startDate, Carbon $endDate, array $filters = []): Collection
    {
        $query = RevenueEvent::query()
            ->whereBetween('recognized_at', [$startDate, $endDate])
            ->where('line_item_type', 'tax')
            ->select([
                DB::raw('DATE(recognized_at) as tax_date'),
                DB::raw('SUM(amount) as tax_amount'),
                DB::raw('COUNT(DISTINCT transaction_id) as transaction_count'),
                DB::raw('tax_rate * 100 as tax_rate_percentage'),
            ])
            ->groupBy('tax_date', 'tax_rate')
            ->orderBy('tax_date');

        // Apply filters
        $this->applyFilters($query, $filters);

        return $query->get();
    }

    /**
     * Get tips report
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $filters
     * @return Collection
     */
    public function getTipsReport(Carbon $startDate, Carbon $endDate, array $filters = []): Collection
    {
        $query = RevenueEvent::query()
            ->with('staff')
            ->whereBetween('recognized_at', [$startDate, $endDate])
            ->where('line_item_type', 'tip')
            ->select([
                'staff_id',
                DB::raw('SUM(amount) as total_tips'),
                DB::raw('COUNT(DISTINCT transaction_id) as transaction_count'),
                DB::raw('AVG(amount) as average_tip'),
            ])
            ->groupBy('staff_id')
            ->orderBy('total_tips', 'desc');

        // Apply filters
        $this->applyFilters($query, $filters);

        return $query->get();
    }

    /**
     * Get discount usage report
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $filters
     * @return Collection
     */
    public function getDiscountsReport(Carbon $startDate, Carbon $endDate, array $filters = []): Collection
    {
        $query = TransactionLineItem::query()
            ->where('line_item_type', 'discount')
            ->whereHas('transaction', function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->select([
                'line_item_id',
                'name',
                DB::raw('COUNT(*) as usage_count'),
                DB::raw('SUM(amount) as total_discount_amount'),
                DB::raw('AVG(amount) as average_discount'),
                DB::raw('COUNT(DISTINCT transaction_id) as transaction_count'),
            ])
            ->groupBy('line_item_id', 'name')
            ->orderBy('total_discount_amount', 'desc');

        // Apply filters
        if (!empty($filters['location_id'])) {
            $query->whereHas('transaction', function ($q) use ($filters) {
                $q->where('location_id', $filters['location_id']);
            });
        }

        if (!empty($filters['staff_id'])) {
            $query->whereHas('transaction', function ($q) use ($filters) {
                $q->where('staff_id', $filters['staff_id']);
            });
        }

        return $query->get();
    }

    /**
     * Get revenue by payment method
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $filters
     * @return Collection
     */
    public function getRevenueByPaymentMethod(Carbon $startDate, Carbon $endDate, array $filters = []): Collection
    {
        $query = RevenueEvent::query()
            ->with('paymentMethod')
            ->whereBetween('event_date', [$startDate, $endDate])
            ->select([
                'payment_method_id',
                DB::raw('SUM(amount) as total_revenue'),
                DB::raw('COUNT(DISTINCT transaction_id) as transaction_count'),
                DB::raw('AVG(amount) as average_transaction_value'),
            ])
            ->groupBy('payment_method_id')
            ->orderBy('total_revenue', 'desc');

        // Apply filters
        $this->applyFilters($query, $filters);

        return $query->get();
    }

    /**
     * Get revenue by time of day
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $filters
     * @return Collection
     */
    public function getRevenueByTimeOfDay(Carbon $startDate, Carbon $endDate, array $filters = []): Collection
    {
        $query = Transaction::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select([
                DB::raw('HOUR(created_at) as hour_of_day'),
                DB::raw('COUNT(DISTINCT id) as transaction_count'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('AVG(total_amount) as average_transaction_value'),
            ])
            ->groupBy('hour_of_day')
            ->orderBy('hour_of_day');

        // Apply filters
        if (!empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        if (!empty($filters['day_of_week'])) {
            $query->whereRaw('DAYOFWEEK(created_at) = ?', [$filters['day_of_week']]);
        }

        $results = $query->get();

        // Fill in missing hours with zero values
        $formattedResults = collect();
        for ($hour = 0; $hour < 24; $hour++) {
            $record = $results->firstWhere('hour_of_day', $hour);
            $formattedResults->push([
                'hour_of_day' => $hour,
                'hour_display' => date('g A', strtotime("$hour:00:00")),
                'transaction_count' => $record->transaction_count ?? 0,
                'total_revenue' => $record->total_revenue ?? 0,
                'average_transaction_value' => $record->average_transaction_value ?? 0,
            ]);
        }

        return $formattedResults;
    }

    /**
     * Get service category report
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $filters
     * @return Collection
     */
    public function getServiceCategoryReport(Carbon $startDate, Carbon $endDate, array $filters = []): Collection
    {
        $query = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('services', 'services.id', '=', 'order_items.itemable_id')
            ->join('service_categories', 'service_categories.id', '=', 'services.category_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('order_items.itemable_type', 'App\Models\Service')
            ->select([
                'service_categories.id as category_id',
                'service_categories.name as category_name',
                DB::raw('SUM(order_items.quantity * order_items.price) as total_revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as transaction_count'),
                DB::raw('AVG(order_items.quantity * order_items.price) as average_transaction_value'),
            ])
            ->groupBy('service_categories.id', 'service_categories.name')
            ->orderBy('total_revenue', 'desc');

        // Apply filters
        $this->applyServiceCategoryFilters($query, $filters);

        return $query->get();
    }

    /**
     * Apply filters to the service category query
     * 
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $filters
     * @return void
     */
    protected function applyServiceCategoryFilters($query, array $filters = []): void
    {
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            
            switch ($key) {
                case 'category_id':
                    $query->where('service_categories.id', $value);
                    break;
                case 'service_id':
                    $query->where('order_items.itemable_id', $value)
                          ->where('order_items.itemable_type', 'App\Models\Service');
                    break;
                case 'staff_id':
                    $query->where('orders.staff_id', $value);
                    break;
                case 'location_id':
                    $query->where('orders.location_id', $value);
                    break;
                // Add more filter cases as needed
            }
        }
    }

    /**
     * Apply filters to the query
     * 
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $filters
     * @return void
     */
    protected function applyFilters($query, array $filters = []): void
    {
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            
            switch ($key) {
                case 'location_id':
                    $query->where('location_id', $value);
                    break;
                case 'staff_id':
                    $query->where('staff_id', $value);
                    break;
                case 'service_id':
                    $query->where('service_id', $value);
                    break;
                case 'product_id':
                    $query->where('product_id', $value);
                    break;
                case 'payment_method_id':
                    $query->where('payment_method_id', $value);
                    break;
                case 'client_id':
                    $query->where('client_id', $value);
                    break;
                // Add more filter cases as needed
            }
        }
    }

    /**
     * Get the date format string for a given period
     */
    protected function getDateFormatForPeriod(string $period): string
    {
        return match (strtolower($period)) {
            'yearly' => '%Y',
            'monthly' => '%Y-%m',
            'weekly' => '%x-%v', // ISO year and week
            'daily' => '%Y-%m-%d',
            'hourly' => '%Y-%m-%d %H:00:00',
            default => '%Y-%m-%d',
        };
    }

    /**
     * Fill in missing periods with zero values
     */
    protected function fillMissingPeriods(Collection $data, Carbon $startDate, Carbon $endDate, string $period): Collection
    {
        $periods = collect();
        
        // Create a period iterator based on the period type
        $dateFormat = $this->getDateFormatForPeriod($period);
        $interval = $this->getDateIntervalForPeriod($period);
        
        $period = CarbonPeriod::create($startDate, $interval, $endDate);
        
        foreach ($period as $date) {
            $periodKey = $date->format($dateFormat);
            $existing = $data->firstWhere('period', $periodKey);
            
            if ($existing) {
                $periods->push($existing);
            } else {
                $periods->push((object)[
                    'period' => $periodKey,
                    'revenue' => 0,
                    'transaction_count' => 0,
                    'service_revenue' => 0,
                    'product_revenue' => 0,
                ]);
            }
        }
        
        return $periods;
    }
    
    /**
     * Get the DateInterval for a period type
     */
    protected function getDateIntervalForPeriod(string $period): string
    {
        return match (strtolower($period)) {
            'yearly' => '1 year',
            'monthly' => '1 month',
            'weekly' => '1 week',
            'hourly' => '1 hour',
            default => '1 day',
        };
    }
}
