<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxRate;
use App\Models\PaymentMethod;
use App\Services\FinancialReportingService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display the tax reports page.
     *
     * @return \Illuminate\View\View
     */
    public function tax()
    {
        // Get active tax rates for the filter dropdown
        $taxRates = TaxRate::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'rate', 'type']);

        // Default date range (last 30 days)
        $defaultStartDate = now()->subDays(30)->format('Y-m-d');
        $defaultEndDate = now()->format('Y-m-d');

        return view('admin.reports.tax', [
            'taxRates' => $taxRates,
            'defaultStartDate' => $defaultStartDate,
            'defaultEndDate' => $defaultEndDate,
        ]);
    }

    /**
     * Display the sales reports page.
     *
     * @return \Illuminate\View\View
     */
    public function sales()
    {
        // Default date range (last 30 days)
        $defaultStartDate = now()->subDays(30)->format('Y-m-d');
        $defaultEndDate = now()->format('Y-m-d');

        return view('admin.reports.sales', [
            'defaultStartDate' => $defaultStartDate,
            'defaultEndDate' => $defaultEndDate,
        ]);
    }

    /**
     * Display the payment method reports page.
     *
     * @return \Illuminate\View\View
     */
    public function paymentMethods(Request $request, FinancialReportingService $reportingService)
    {
        // Get all active payment methods for the filter dropdown
        $paymentMethods = PaymentMethod::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Default date range (last 30 days)
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $paymentMethodId = $request->input('payment_method_id');

        // Convert to Carbon instances for the service
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Prepare filters
        $filters = [];
        if ($paymentMethodId) {
            $filters['payment_method_id'] = $paymentMethodId;
        }

        // Get the report data
        $reportData = $reportingService->getRevenueByPaymentMethod($start, $end, $filters);

        return view('admin.reports.payment-methods', [
            'reportData' => $reportData,
            'paymentMethods' => $paymentMethods,
            'selectedPaymentMethod' => $paymentMethodId,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}
