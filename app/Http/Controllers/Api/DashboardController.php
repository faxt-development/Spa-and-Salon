<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Get staff statistics for the dashboard
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStaffStats()
    {
        try {
            // Get total number of staff
            $totalStaff = Staff::count();

            // Get active staff count
            $activeStaff = Staff::where('active', true)->count();

            // For simplicity, we'll consider staff as available if they are active and working today
            // In a real app, you might want to check their schedule and current appointments
            $availableStaff = Staff::where('active', true)
                ->where(function($query) {
                    // Check if today is one of their working days
                    $dayOfWeek = strtolower(Carbon::now()->format('l'));
                    $query->whereJsonContains('work_days', $dayOfWeek)
                          ->orWhereNull('work_days');
                })
                ->count();

            return response()->json([
                'total_staff' => $totalStaff,
                'active_staff' => $activeStaff,
                'available_staff' => $availableStaff
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching staff stats: ' . $e->getMessage());

            return response()->json([
                'total_staff' => 0,
                'active_staff' => 0,
                'available_staff' => 0,
                'error' => 'Failed to fetch staff statistics'
            ], 500);
        }
    }

       /**
     * Get today's revenue statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRevenueStats()
    {
        try {
            // Get today's date at 00:00:00
            $today = now()->startOfDay();
            
            // Calculate today's revenue by summing all completed payments for today
            $todayRevenue = \App\Models\Payment::where('status', 'completed')
                ->whereDate('payment_date', $today)
                ->sum('amount');
            
            // Define daily revenue target (in a real app, this might come from a settings table)
            $dailyTarget = 1500.00;
            
            // Calculate target percentage (capped at 100%)
            $targetPercentage = $dailyTarget > 0 
                ? min(round(($todayRevenue / $dailyTarget) * 100), 100) 
                : 0;
                
            // Check if target is reached
            $targetReached = $todayRevenue >= $dailyTarget;
            
            return response()->json([
                'today_revenue' => (float) number_format($todayRevenue, 2, '.', ''),
                'target_percentage' => (int) $targetPercentage,
                'target_reached' => $targetReached
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching revenue stats: ' . $e->getMessage());
            
            // Return default values in case of error
            return response()->json([
                'today_revenue' => 0,
                'target_percentage' => 0,
                'target_reached' => false,
                'error' => 'Failed to fetch revenue statistics'
            ], 500);
        }
    }

}
