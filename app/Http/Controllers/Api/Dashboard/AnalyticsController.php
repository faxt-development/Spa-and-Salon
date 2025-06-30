<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\PerformanceAnalyticsService;
use App\Services\RevenueAnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    /**
     * The revenue analytics service instance.
     */
    protected RevenueAnalyticsService $revenueAnalytics;

    /**
     * The performance analytics service instance.
     */
    protected PerformanceAnalyticsService $performanceAnalytics;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        RevenueAnalyticsService $revenueAnalytics,
        PerformanceAnalyticsService $performanceAnalytics
    ) {
        $this->middleware('auth:api');
        $this->revenueAnalytics = $revenueAnalytics;
        $this->performanceAnalytics = $performanceAnalytics;
    }

    /**
     * Get revenue trends.
     */
    public function revenueTrends(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period' => 'required|in:daily,weekly,monthly',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after_or_equal:start_date',
            'location_id' => 'sometimes|integer|exists:locations,id',
        ]);

        $locationId = $validated['location_id'] ?? null;
        $endDate = isset($validated['end_date']) 
            ? Carbon::parse($validated['end_date'])
            : now();
            
        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])
            : $endDate->copy()->subMonths(3);

        switch ($validated['period']) {
            case 'daily':
                $data = $this->revenueAnalytics->getDailyTrends($startDate, $endDate, $locationId);
                break;
            case 'weekly':
                $data = $this->revenueAnalytics->getWeeklyTrends(
                    $startDate->diffInWeeks($endDate) + 1,
                    $locationId
                );
                break;
            case 'monthly':
            default:
                $data = $this->revenueAnalytics->getMonthlyTrends(
                    $startDate->diffInMonths($endDate) + 1,
                    $locationId
                );
                break;
        }

        return response()->json([
            'data' => $data,
            'meta' => [
                'period' => $validated['period'],
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'location_id' => $locationId,
            ],
        ]);
    }

    /**
     * Get top performing services.
     */
    public function topServices(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'limit' => 'sometimes|integer|min:1|max:10',
            'period' => 'sometimes|in:week,month,quarter,year',
            'location_id' => 'sometimes|integer|exists:locations,id',
        ]);

        $endDate = now();
        $startDate = clone $endDate;

        switch ($validated['period'] ?? 'month') {
            case 'week':
                $startDate->subWeek();
                break;
            case 'quarter':
                $startDate->subQuarter();
                break;
            case 'year':
                $startDate->subYear();
                break;
            case 'month':
            default:
                $startDate->subMonth();
                break;
        }

        $services = $this->performanceAnalytics->getTopServicesByRevenue(
            $startDate,
            $endDate,
            $validated['limit'] ?? 5,
            $validated['location_id'] ?? null
        );

        return response()->json([
            'data' => $services,
            'meta' => [
                'period' => $validated['period'] ?? 'month',
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'location_id' => $validated['location_id'] ?? null,
            ],
        ]);
    }

    /**
     * Get top performing staff.
     */
    public function topStaff(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'limit' => 'sometimes|integer|min:1|max:10',
            'period' => 'sometimes|in:week,month,quarter,year',
            'location_id' => 'sometimes|integer|exists:locations,id',
        ]);

        $endDate = now();
        $startDate = clone $endDate;

        switch ($validated['period'] ?? 'month') {
            case 'week':
                $startDate->subWeek();
                break;
            case 'quarter':
                $startDate->subQuarter();
                break;
            case 'year':
                $startDate->subYear();
                break;
            case 'month':
            default:
                $startDate->subMonth();
                break;
        }

        $staff = $this->performanceAnalytics->getTopStaffByRevenue(
            $startDate,
            $endDate,
            $validated['limit'] ?? 5,
            $validated['location_id'] ?? null
        );

        return response()->json([
            'data' => $staff,
            'meta' => [
                'period' => $validated['period'] ?? 'month',
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'location_id' => $validated['location_id'] ?? null,
            ],
        ]);
    }

    /**
     * Get performance metrics for a specific service.
     */
    public function serviceMetrics(string $serviceId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period' => 'sometimes|in:week,month,year',
        ]);

        $metrics = $this->performanceAnalytics->getServicePerformanceMetrics(
            $serviceId,
            $validated['period'] ?? 'month'
        );

        return response()->json($metrics);
    }

    /**
     * Get performance metrics for a specific staff member.
     */
    public function staffMetrics(string $staffId, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period' => 'sometimes|in:week,month,year',
        ]);

        $metrics = $this->performanceAnalytics->getStaffPerformanceMetrics(
            $staffId,
            $validated['period'] ?? 'month'
        );

        return response()->json($metrics);
    }

    /**
     * Get revenue by location.
     */
    public function revenueByLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period' => 'sometimes|in:week,month,quarter,year',
        ]);

        $endDate = now();
        $startDate = clone $endDate;

        switch ($validated['period'] ?? 'month') {
            case 'week':
                $startDate->subWeek();
                break;
            case 'quarter':
                $startDate->subQuarter();
                break;
            case 'year':
                $startDate->subYear();
                break;
            case 'month':
            default:
                $startDate->subMonth();
                break;
        }

        $data = $this->revenueAnalytics->getRevenueByLocation(
            $startDate,
            $endDate
        );

        return response()->json([
            'data' => $data,
            'meta' => [
                'period' => $validated['period'] ?? 'month',
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
        ]);
    }
}
