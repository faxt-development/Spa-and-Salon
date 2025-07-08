<?php

namespace App\Http\Controllers\Api\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\DashboardWidget;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class WidgetController extends Controller
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
     * Get all widgets for the authenticated user's dashboard.
     */
    public function index(): JsonResponse
    {
        $widgets = $this->dashboardService->getUserWidgets(Auth::user());
        return response()->json(['data' => $widgets]);
    }

    /**
     * Add a new widget to the dashboard.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:' . implode(',', array_keys($this->dashboardService->getAvailableWidgetTypes())),
            'settings' => 'sometimes|array',
        ]);

        $widget = $this->dashboardService->addWidget(
            Auth::user(),
            $validated['type'],
            $validated['settings'] ?? []
        );

        return response()->json([
            'message' => 'Widget added successfully',
            'data' => $widget,
        ], Response::HTTP_CREATED);
    }

    /**
     * Get a specific widget.
     */
    public function show(DashboardWidget $widget): JsonResponse
    {
        $this->authorize('view', $widget);
        return response()->json(['data' => $widget]);
    }

    /**
     * Update a widget's settings or position.
     */
    public function update(Request $request, DashboardWidget $widget): JsonResponse
    {
        $this->authorize('update', $widget);

        $validated = $request->validate([
            'position' => 'sometimes|integer|min:0',
            'is_visible' => 'sometimes|boolean',
            'settings' => 'sometimes|array',
        ]);

        $widget = $this->dashboardService->updateWidget(
            Auth::user(),
            $widget->id,
            $validated
        );

        return response()->json([
            'message' => 'Widget updated successfully',
            'data' => $widget,
        ]);
    }

    /**
     * Remove a widget from the dashboard.
     */
    public function destroy(DashboardWidget $widget): JsonResponse
    {
        $this->authorize('delete', $widget);

        $this->dashboardService->removeWidget(Auth::user(), $widget->id);

        return response()->json([
            'message' => 'Widget removed successfully',
        ]);
    }
}
