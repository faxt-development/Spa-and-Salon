<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxRate;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
}
