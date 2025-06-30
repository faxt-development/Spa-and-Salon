<?php

namespace App\Services;

use App\Models\RevenueSnapshot;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RevenueAnalyticsService
{
    /**
     * Get daily revenue trends for a given date range
     */
    public function getDailyTrends(Carbon $startDate, Carbon $endDate, ?int $locationId = null): Collection
    {
        $query = RevenueSnapshot::whereBetween('snapshot_date', [$startDate, $endDate])
            ->orderBy('snapshot_date');

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        return $query->get()
            ->groupBy('snapshot_date')
            ->map(function ($snapshots, $date) {
                return [
                    'date' => $date,
                    'revenue' => $snapshots->sum('amount'),
                    'count' => $snapshots->count(),
                ];
            })
            ->values();
    }

    /**
     * Get weekly revenue trends for a number of weeks
     */
    public function getWeeklyTrends(int $weeks = 12, ?int $locationId = null): Collection
    {
        $endDate = now()->endOfWeek();
        $startDate = now()->subWeeks($weeks)->startOfWeek();

        $query = RevenueSnapshot::whereBetween('snapshot_date', [$startDate, $endDate])
            ->orderBy('snapshot_date');

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        return $query->get()
            ->groupBy(function ($snapshot) {
                return $snapshot->snapshot_date->format('Y-W');
            })
            ->map(function ($snapshots, $week) {
                $firstSnapshot = $snapshots->first();
                return [
                    'week' => $week,
                    'year' => $firstSnapshot->snapshot_date->year,
                    'week_number' => $firstSnapshot->snapshot_date->weekOfYear,
                    'start_date' => $firstSnapshot->snapshot_date->startOfWeek()->toDateString(),
                    'end_date' => $firstSnapshot->snapshot_date->endOfWeek()->toDateString(),
                    'revenue' => $snapshots->sum('amount'),
                    'count' => $snapshots->count(),
                ];
            })
            ->values();
    }

    /**
     * Get monthly revenue trends for a number of months
     */
    public function getMonthlyTrends(int $months = 12, ?int $locationId = null): Collection
    {
        $endDate = now()->endOfMonth();
        $startDate = now()->subMonths($months - 1)->startOfMonth();

        $query = RevenueSnapshot::whereBetween('snapshot_date', [$startDate, $endDate])
            ->orderBy('snapshot_date');

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        return $query->get()
            ->groupBy(function ($snapshot) {
                return $snapshot->snapshot_date->format('Y-m');
            })
            ->map(function ($snapshots, $month) {
                $firstSnapshot = $snapshots->first();
                return [
                    'month' => $month,
                    'year' => $firstSnapshot->snapshot_date->year,
                    'month_number' => $firstSnapshot->snapshot_date->month,
                    'month_name' => $firstSnapshot->snapshot_date->monthName,
                    'revenue' => $snapshots->sum('amount'),
                    'count' => $snapshots->count(),
                ];
            })
            ->values();
    }

    /**
     * Generate daily revenue snapshot for the given date
     */
    public function generateDailySnapshot(\DateTime $date): void
    {
        // This would be implemented to aggregate payment data for the day
        // and create a RevenueSnapshot record
        // Implementation depends on your payment and booking structure
    }

    /**
     * Get revenue by location for a given date range
     */
    public function getRevenueByLocation(Carbon $startDate, Carbon $endDate): Collection
    {
        $query = RevenueSnapshot::whereBetween('snapshot_date', [$startDate, $endDate])
            ->join('locations', 'revenue_snapshots.location_id', '=', 'locations.id')
            ->select(
                'locations.id as location_id',
                'locations.name as location_name',
                \DB::raw('SUM(revenue_snapshots.amount) as revenue'),
                \DB::raw('COUNT(revenue_snapshots.id) as count')
            )
            ->groupBy('locations.id', 'locations.name')
            ->orderByDesc('revenue');

        return $query->get();
    }
}
