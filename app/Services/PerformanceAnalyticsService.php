<?php

namespace App\Services;

use App\Models\Service;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PerformanceAnalyticsService
{
    /**
     * Get top performing services by revenue
     */
    public function getTopServicesByRevenue(Carbon $startDate, Carbon $endDate, int $limit = 5, ?int $locationId = null): Collection
    {
        $query = Service::withCount(['appointments' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_time', [$startDate, $endDate])
                ->whereIn('status', ['completed', 'confirmed']);
        }])
        ->withSum(['appointments' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_time', [$startDate, $endDate])
                ->whereIn('status', ['completed', 'confirmed']);
        }], 'price')
        ->orderByDesc('appointments_sum_price')
        ->limit($limit);

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        return $query->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'revenue' => (float) $service->appointments_sum_price,
                    'booking_count' => $service->appointments_count,
                    'average_revenue' => $service->appointments_count > 0 
                        ? $service->appointments_sum_price / $service->appointments_count 
                        : 0,
                ];
            });
    }

    /**
     * Get top performing staff by revenue
     */
    public function getTopStaffByRevenue(Carbon $startDate, Carbon $endDate, int $limit = 5, ?int $locationId = null): Collection
    {
        $query = Staff::withCount(['appointments' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_time', [$startDate, $endDate])
                ->whereIn('status', ['completed', 'confirmed']);
        }])
        ->withSum(['appointments' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_time', [$startDate, $endDate])
                ->whereIn('status', ['completed', 'confirmed']);
        }], 'price')
        ->orderByDesc('appointments_sum_price')
        ->limit($limit);

        if ($locationId) {
            $query->where('location_id', $locationId);
        }

        return $query->get()
            ->map(function ($staff) {
                return [
                    'id' => $staff->id,
                    'name' => $staff->name,
                    'revenue' => (float) $staff->appointments_sum_price,
                    'appointment_count' => $staff->appointments_count,
                    'average_revenue' => $staff->appointments_count > 0 
                        ? $staff->appointments_sum_price / $staff->appointments_count 
                        : 0,
                ];
            });
    }

    /**
     * Get service performance metrics
     */
    public function getServicePerformanceMetrics(int $serviceId, string $period = 'month'): array
    {
        $endDate = now();
        $startDate = clone $endDate;
        
        switch ($period) {
            case 'week':
                $startDate->subWeek();
                break;
            case 'month':
            default:
                $startDate->subMonth();
                break;
            case 'year':
                $startDate->subYear();
                break;
        }

        $service = Service::withCount(['appointments' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_time', [$startDate, $endDate]);
        }])
        ->withSum(['appointments' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_time', [$startDate, $endDate]);
        }], 'price')
        ->findOrFail($serviceId);

        // Calculate utilization rate (assuming service has a duration field)
        $totalPossibleSlots = 0; // This would be calculated based on business hours and staff availability
        $utilizationRate = 0; // This would be calculated based on actual bookings vs available slots

        return [
            'service' => [
                'id' => $service->id,
                'name' => $service->name,
                'revenue' => (float) $service->appointments_sum_price,
                'booking_count' => $service->appointments_count,
                'average_rating' => $service->reviews_avg_rating ?? 0,
                'utilization_rate' => $utilizationRate,
                'period' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
            ],
        ];
    }

    /**
     * Get staff performance metrics
     */
    public function getStaffPerformanceMetrics(int $staffId, string $period = 'month'): array
    {
        $endDate = now();
        $startDate = clone $endDate;
        
        switch ($period) {
            case 'week':
                $startDate->subWeek();
                break;
            case 'month':
            default:
                $startDate->subMonth();
                break;
            case 'year':
                $startDate->subYear();
                break;
        }

        $staff = Staff::withCount(['appointments' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_time', [$startDate, $endDate]);
        }])
        ->withSum(['appointments' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('start_time', [$startDate, $endDate]);
        }], 'price')
        ->withAvg('reviews', 'rating')
        ->findOrFail($staffId);

        // Calculate utilization rate
        $totalPossibleHours = 0; // This would be calculated based on staff schedule
        $utilizationRate = 0; // This would be calculated based on booked hours vs available hours

        return [
            'staff' => [
                'id' => $staff->id,
                'name' => $staff->name,
                'revenue' => (float) $staff->appointments_sum_price,
                'appointment_count' => $staff->appointments_count,
                'average_rating' => $staff->reviews_avg_rating ?? 0,
                'utilization_rate' => $utilizationRate,
                'period' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
            ],
        ];
    }
}
