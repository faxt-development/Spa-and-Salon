<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Product;

class DashboardController extends Controller
{
    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = auth()->user();
        $isNewAdmin = $this->isNewAdmin($user);
        
        return view('admin.dashboard', [
            'title' => 'Admin Dashboard',
            'isNewAdmin' => $isNewAdmin
        ]);
    }
    
    /**
     * Check if the user is a new admin (registered within the last 14 days)
     *
     * @param \App\Models\User $user
     * @return bool
     */
    private function isNewAdmin($user)
    {
        // Consider an admin as new if they registered within the last 14 days
        return $user && $user->hasRole('admin') && $user->created_at->diffInDays(now()) <= 14;
    }

    /**
     * Get today's appointments for the dashboard.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTodaysSchedule()
    {
        $today = Carbon::today();

        $appointments = Appointment::with(['client', 'services', 'staff'])
            ->whereDate('start_time', $today)
            ->orderBy('start_time')
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'client_name' => $appointment->client->name,
                    'services' => $appointment->services->pluck('name')->implode(', '),
                    'start_time' => $appointment->start_time->format('g:i A'),
                    'end_time' => $appointment->end_time->format('g:i A'),
                    'staff_name' => $appointment->staff->name,
                    'status' => $appointment->status,
                    'status_label' => ucfirst(str_replace('_', ' ', $appointment->status)),
                ];
            });

        return response()->json($appointments);
    }

    /**
     * Get alerts for the dashboard.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAlerts()
    {
        $alerts = [];

        // Get low stock products (assuming threshold is 5)
        $lowStockProducts = Product::where('quantity', '<=', 5)
            ->where('quantity', '>', 0)
            ->orderBy('quantity')
            ->get();

        foreach ($lowStockProducts as $product) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Low Stock Alert',
                'message' => "{$product->name} is running low ({$product->quantity} remaining)"
            ];
        }

        // Get today's no-shows
        $today = Carbon::today();
        $noShows = Appointment::whereDate('start_time', $today)
            ->where('status', 'no_show')
            ->count();

        if ($noShows > 0) {
            $alerts[] = [
                'type' => 'error',
                'title' => "{$noShows} No-show" . ($noShows > 1 ? 's' : '') . " Today",
                'message' => 'Consider following up with these clients'
            ];
        }

        // If no alerts, add a default message
        if (empty($alerts)) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'No Alerts',
                'message' => 'You\'re all caught up!'
            ];
        }

        return response()->json($alerts);
    }
}
