<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getRevenueSummary(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, array $filters = [])
 * @method static \Illuminate\Support\Collection getRevenueByPeriod(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, string $period = 'daily', array $filters = [])
 * @method static \Illuminate\Support\Collection getRevenueByCategory(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, array $filters = [])
 * @method static \Illuminate\Support\Collection getRevenueByStaff(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, array $filters = [])
 * @method static \Illuminate\Support\Collection getRevenueByLocation(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, array $filters = [])
 * @method static \Illuminate\Support\Collection getTopServices(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, int $limit = 10, array $filters = [])
 * @method static \Illuminate\Support\Collection getTopProducts(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, int $limit = 10, array $filters = [])
 * @method static \Illuminate\Support\Collection getTopCustomers(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, int $limit = 10, array $filters = [])
 * @method static \Illuminate\Support\Collection getCommissionReport(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, array $filters = [])
 * @method static \Illuminate\Support\Collection getSalesTaxReport(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, array $filters = [])
 * @method static \Illuminate\Support\Collection getTipsReport(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, array $filters = [])
 * @method static \Illuminate\Support\Collection getDiscountsReport(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, array $filters = [])
 * @method static \Illuminate\Support\Collection getRevenueByTimeOfDay(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, array $filters = [])
 * 
 * @see \App\Services\FinancialReportingService
 */
class FinancialReports extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'financial-reports';
    }
}
