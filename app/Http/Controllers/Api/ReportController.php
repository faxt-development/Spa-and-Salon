<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\TaxRate;
use App\Models\PayrollRecord;
use App\Models\OrderItem;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    /**
     * Get the SQL expression for grouping by a specific time period
     *
     * @param string $period
     * @param string $column
     * @param bool $forGroupBy
     * @return \Illuminate\Database\Query\Expression|string
     */
    protected function getGroupByExpression($period, $column, $forGroupBy = false)
    {
        $driver = config('database.default');
        
        if ($period === 'tax_rate') {
            return $forGroupBy ? 'tax_rates.id' : 'tax_rates.id as tax_rate_id';
        }
        
        switch ($period) {
            case 'day':
                $format = $driver === 'mysql' 
                    ? "DATE_FORMAT($column, '%Y-%m-%d')" 
                    : "strftime('%Y-%m-%d', $column)";
                $alias = 'day';
                break;
                
            case 'week':
                $format = $driver === 'mysql'
                    ? "DATE_FORMAT($column, '%x-%v')" 
                    : "strftime('%Y-%W', $column)";
                $alias = 'week';
                break;
                
            case 'quarter':
                $format = $driver === 'mysql'
                    ? "CONCAT(YEAR($column), '-Q', QUARTER($column))"
                    : "strftime('%Y-', $column) || ((strftime('%m', $column) + 2) / 3)";
                $alias = 'quarter';
                break;
                
            case 'year':
                $format = $driver === 'mysql'
                    ? "YEAR($column)"
                    : "strftime('%Y', $column)";
                $alias = 'year';
                break;
                
            case 'month':
            default:
                $format = $driver === 'mysql'
                    ? "DATE_FORMAT($column, '%Y-%m')"
                    : "strftime('%Y-%m', $column)";
                $alias = 'month';
                break;
        }
        
        return $forGroupBy ? DB::raw($format) : DB::raw("$format as period");
    }
    
    /**
     * Format the period for display based on the group by type
     * 
     * @param string $period
     * @param string $value
     * @return string
     */
    protected function formatPeriod($period, $value)
    {
        if ($period === 'tax_rate') {
            return $value;
        }
        
        try {
            switch ($period) {
                case 'day':
                    return Carbon::parse($value)->format('M j, Y');
                case 'week':
                    list($year, $week) = explode('-', $value);
                    $date = Carbon::now();
                    $date->setISODate($year, $week);
                    return 'Week of ' . $date->startOfWeek()->format('M j, Y');
                case 'month':
                    return Carbon::parse($value . '-01')->format('F Y');
                case 'quarter':
                    list($year, $q) = explode('-Q', $value);
                    $month = ($q - 1) * 3 + 1;
                    return 'Q' . $q . ' ' . $year;
                case 'year':
                    return $value;
                default:
                    return $value;
            }
        } catch (\Exception $e) {
            return $value;
        }
    }
    /**
     * Generate tax summary report
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function taxSummary(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'group_by' => 'nullable|in:day,week,month,quarter,year,tax_rate',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();
        $groupBy = $request->group_by ?? 'month';

        // Base query for order taxes
        $orderTaxes = Order::select(
            DB::raw('"order" as source'),
            'tax_rates.name as tax_rate_name',
            'tax_rates.rate as tax_rate',
            'tax_rates.type as tax_type',
            DB::raw('SUM(order_items.quantity * order_items.unit_price) as taxable_amount'),
            DB::raw('SUM(order_items.tax_amount) as tax_amount'),
            $this->getGroupByExpression($groupBy, 'orders.created_at')
        )
        ->join('order_items', 'orders.id', '=', 'order_items.order_id')
        ->leftJoin('tax_rates', 'order_items.tax_rate_id', '=', 'tax_rates.id')
        ->whereBetween('orders.created_at', [$startDate, $endDate])
        ->where('orders.status', '!=', 'cancelled')
        ->groupBy('tax_rates.id', $this->getGroupByExpression($groupBy, 'orders.created_at', true))
        ->orderBy('period');

        // Base query for payroll taxes
        $payrollTaxes = PayrollRecord::select(
            DB::raw('"payroll" as source'),
            DB::raw('"Payroll Tax" as tax_rate_name'),
            DB::raw('0 as tax_rate'),
            DB::raw('"payroll" as tax_type'),
            DB::raw('gross_amount as taxable_amount'),
            'tax_amount',
            $this->getGroupByExpression($groupBy, 'pay_period_start')
        )
        ->whereBetween('pay_period_start', [$startDate, $endDate])
        ->where('payment_status', 'processed')
        ->groupBy('period', 'tax_amount', 'gross_amount');

        // Combine and process results
        $results = $orderTaxes->get()
            ->merge($payrollTaxes->get())
            ->groupBy('period')
            ->map(function ($periodItems) {
                return [
                    'period' => $periodItems->first()->period,
                    'total_taxable_amount' => $periodItems->sum('taxable_amount'),
                    'total_tax_amount' => $periodItems->sum('tax_amount'),
                    'breakdown' => $periodItems->map(function ($item) {
                        return [
                            'source' => $item->source,
                            'tax_rate_name' => $item->tax_rate_name,
                            'tax_rate' => (float) $item->tax_rate,
                            'tax_type' => $item->tax_type,
                            'taxable_amount' => (float) $item->taxable_amount,
                            'tax_amount' => (float) $item->tax_amount,
                        ];
                    })->values()
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'group_by' => $groupBy,
                'total_taxable_amount' => $results->sum('total_taxable_amount'),
                'total_tax_amount' => $results->sum('total_tax_amount'),
                'results' => $results,
            ]
        ]);
    }

    /**
     * Generate detailed tax report
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function taxDetailed(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'tax_rate_id' => 'nullable|exists:tax_rates,id',
            'type' => 'nullable|in:order,payroll,all',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();
        $perPage = $request->per_page ?? 25;

        $query = null;
        
        // Handle order taxes
        if (in_array($request->type, [null, 'all', 'order'])) {
            $orderQuery = Order::with(['client', 'items.taxRate'])
                ->select('orders.*')
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->whereBetween('orders.created_at', [$startDate, $endDate])
                ->where('orders.status', '!=', 'cancelled');

            if ($request->tax_rate_id) {
                $orderQuery->where('order_items.tax_rate_id', $request->tax_rate_id);
            }

            $orderQuery->selectRaw('SUM(order_items.tax_amount) as total_tax')
                ->groupBy('orders.id');

            $query = $orderQuery;
        }

        // Handle payroll taxes if needed
        if (in_array($request->type, [null, 'all', 'payroll'])) {
            $payrollQuery = PayrollRecord::with(['employee'])
                ->whereBetween('pay_period_start', [$startDate, $endDate])
                ->where('payment_status', 'processed');

            if ($request->type === 'payroll') {
                $query = $payrollQuery;
            } else {
                // Union with order query if needed
                $query = $query ? $query->union($payrollQuery) : $payrollQuery;
            }
        }

        if (!$query) {
            return response()->json([
                'success' => true,
                'data' => [
                    'data' => [],
                    'total' => 0,
                    'current_page' => 1,
                    'per_page' => $perPage,
                    'last_page' => 1,
                ]
            ]);
        }

        $results = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }
}
