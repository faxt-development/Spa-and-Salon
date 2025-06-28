<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommissionReportResource;
use App\Http\Resources\PerformanceMetricResource;
use App\Http\Resources\StaffPerformanceSummaryResource;
use App\Http\Resources\UtilizationReportResource;
use App\Models\Staff;
use App\Services\CommissionService;
use App\Services\PerformanceMetricsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class StaffPerformanceController extends Controller
{
    public function __construct(
        private PerformanceMetricsService $metricsService,
        private CommissionService $commissionService
    ) {}

    /**
     * Get staff performance metrics
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'period' => ['sometimes', 'in:hour,day,week,month,quarter,year'],
            'staff_id' => ['sometimes', 'exists:staff,id'],
            'location_id' => ['sometimes', 'exists:locations,id'],
        ]);

        $metrics = $this->metricsService->getStaffPerformanceMetrics(
            startDate: Carbon::parse($validated['start_date']),
            endDate: Carbon::parse($validated['end_date']),
            period: $validated['period'] ?? 'day',
            filters: array_filter([
                'staff_id' => $validated['staff_id'] ?? null,
                'location_id' => $validated['location_id'] ?? null,
            ])
        );

        return PerformanceMetricResource::collection($metrics);
    }

    /**
     * Get staff utilization report
     */
    public function utilizationReport(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'staff_id' => ['sometimes', 'exists:staff,id'],
            'location_id' => ['sometimes', 'exists:locations,id'],
        ]);

        $report = $this->metricsService->getUtilizationReport(
            startDate: Carbon::parse($validated['start_date']),
            endDate: Carbon::parse($validated['end_date']),
            filters: array_filter([
                'staff_id' => $validated['staff_id'] ?? null,
                'location_id' => $validated['location_id'] ?? null,
            ])
        );

        return UtilizationReportResource::collection($report);
    }

    /**
     * Get revenue per staff report
     */
    public function revenueReport(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'staff_id' => ['sometimes', 'exists:staff,id'],
            'location_id' => ['sometimes', 'exists:locations,id'],
        ]);

        $report = $this->metricsService->getRevenuePerStaffReport(
            startDate: Carbon::parse($validated['start_date']),
            endDate: Carbon::parse($validated['end_date']),
            filters: array_filter([
                'staff_id' => $validated['staff_id'] ?? null,
                'location_id' => $validated['location_id'] ?? null,
            ])
        );

        return StaffPerformanceSummaryResource::collection($report);
    }

    /**
     * Get commission report
     */
    public function commissionReport(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'staff_id' => ['sometimes', 'exists:staff,id'],
            'location_id' => ['sometimes', 'exists:locations,id'],
            'item_type' => ['sometimes', 'in:service,product'],
        ]);

        $report = $this->commissionService->generateCommissionReport(
            startDate: Carbon::parse($validated['start_date']),
            endDate: Carbon::parse($validated['end_date']),
            filters: array_filter([
                'staff_id' => $validated['staff_id'] ?? null,
                'location_id' => $validated['location_id'] ?? null,
                'item_type' => $validated['item_type'] ?? null,
            ])
        );

        return CommissionReportResource::collection($report);
    }

    /**
     * Get performance summary for a specific staff member
     */
    public function staffSummary(Staff $staff, Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        // Get utilization metrics
        $utilization = $this->metricsService->getUtilizationReport(
            startDate: Carbon::parse($validated['start_date']),
            endDate: Carbon::parse($validated['end_date']),
            filters: ['staff_id' => $staff->id]
        )->first();

        // Get revenue metrics
        $revenue = $this->metricsService->getRevenuePerStaffReport(
            startDate: Carbon::parse($validated['start_date']),
            endDate: Carbon::parse($validated['end_date']),
            filters: ['staff_id' => $staff->id]
        )->first();

        // Get commission data
        $commissions = $this->commissionService->generateCommissionReport(
            startDate: Carbon::parse($validated['start_date']),
            endDate: Carbon::parse($validated['end_date']),
            filters: ['staff_id' => $staff->id]
        );

        return response()->json([
            'staff' => [
                'id' => $staff->id,
                'name' => $staff->full_name,
                'position' => $staff->position,
                'commission_rate' => $staff->commission_rate,
                'commission_structure' => $staff->commissionStructure,
            ],
            'metrics' => [
                'available_hours' => $utilization->available_hours ?? 0,
                'booked_hours' => $utilization->booked_hours ?? 0,
                'utilization_rate' => $utilization->utilization_rate ?? 0,
                'total_revenue' => $revenue->total_revenue ?? 0,
                'revenue_per_hour' => $revenue->revenue_per_hour ?? 0,
                'total_appointments' => $revenue->total_appointments ?? 0,
                'avg_ticket_value' => $revenue->avg_ticket_value ?? 0,
            ],
            'commissions' => $commissions,
        ]);
    }
}
