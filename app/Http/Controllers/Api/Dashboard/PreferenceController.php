<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PreferenceController extends Controller
{
    /**
     * The dashboard service instance.
     */
    protected DashboardService $dashboardService;

    /**
     * Create a new controller instance.
     */
    public function __construct(DashboardService $dashboardService)
    {
        $this->middleware('auth:api');
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get the authenticated user's dashboard preferences.
     */
    public function index(): JsonResponse
    {
        $preferences = $this->dashboardService->getUserPreferences(Auth::user());
        return response()->json(['data' => $preferences]);
    }

    /**
     * Update the authenticated user's dashboard preferences.
     */
    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'layout' => 'sometimes|array',
            'filters' => 'sometimes|array',
            'theme' => 'sometimes|string|in:light,dark,system',
        ]);

        $preferences = $this->dashboardService->updatePreferences(
            Auth::user(),
            $validated
        );

        return response()->json([
            'message' => 'Preferences updated successfully',
            'data' => $preferences,
        ]);
    }

    /**
     * Reset the authenticated user's dashboard to default.
     */
    public function reset(): JsonResponse
    {
        // Delete all user's widgets
        Auth::user()->dashboardWidgets()->delete();
        
        // Reset preferences
        Auth::user()->dashboardPreference()->delete();
        
        // Get fresh default widgets and preferences
        $widgets = $this->dashboardService->getUserWidgets(Auth::user());
        $preferences = $this->dashboardService->getUserPreferences(Auth::user());
        
        return response()->json([
            'message' => 'Dashboard reset to default successfully',
            'data' => [
                'widgets' => $widgets,
                'preferences' => $preferences,
            ],
        ]);
    }
}
