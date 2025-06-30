<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxRate;
use App\Models\PaymentMethod;
use App\Models\ServiceCategory;
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
    
    /**
     * Display the service category reports page.
     *
     * @return \Illuminate\View\View
     */
    public function serviceCategories()
    {
        // Get all service categories for the filter dropdown
        $serviceCategories = ServiceCategory::orderBy('name')
            ->get(['id', 'name']);
            
        // Default date range (last 30 days)
        $defaultStartDate = now()->subDays(30)->format('Y-m-d');
        $defaultEndDate = now()->format('Y-m-d');

        return view('admin.reports.service-categories', [
            'serviceCategories' => $serviceCategories,
            'defaultStartDate' => $defaultStartDate,
            'defaultEndDate' => $defaultEndDate,
        ]);
    }
    
    /**
     * Get service category report data via AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\FinancialReportingService  $reportingService
     * @return \Illuminate\Http\JsonResponse
     */
    public function getServiceCategoryData(Request $request, FinancialReportingService $reportingService)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'category_id' => 'nullable|exists:service_categories,id',
        ]);
        
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        
        $filters = [];
        if ($request->filled('category_id')) {
            $filters['category_id'] = $request->input('category_id');
        }
        
        // Get the report data
        $reportData = $reportingService->getRevenueByServiceCategory($startDate, $endDate, $filters);
        
        return response()->json([
            'success' => true,
            'data' => $reportData,
        ]);
    }
    
    /**
     * Get service performance by category via AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Services\FinancialReportingService  $reportingService
     * @return \Illuminate\Http\JsonResponse
     */
    public function getServicePerformanceData(Request $request, FinancialReportingService $reportingService)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'category_id' => 'nullable|exists:service_categories,id',
        ]);
        
        $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        
        $filters = [];
        if ($request->filled('category_id')) {
            $filters['category_id'] = $request->input('category_id');
        }
        
        // Get the service performance data
        $performanceData = $reportingService->getServicePerformanceByCategory($startDate, $endDate, $filters);
        
        return response()->json([
            'success' => true,
            'data' => $performanceData,
        ]);
    }
}
