<?php

namespace App\Services;

use App\Models\DashboardWidget;
use App\Models\User;
use App\Models\UserDashboardPreference;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * Default dashboard widgets configuration
     */
    protected array $defaultWidgets = [
        [
            'name' => 'Revenue Overview',
            'type' => 'revenue_overview',
            'position' => 0,
            'is_visible' => true,
            'settings' => [
                'timeframe' => 'month',
                'compare' => true,
            ],
        ],
        [
            'name' => 'Top Services',
            'type' => 'top_services',
            'position' => 1,
            'is_visible' => true,
            'settings' => [
                'limit' => 5,
                'timeframe' => 'month',
            ],
        ],
        [
            'name' => 'Top Staff',
            'type' => 'top_staff',
            'position' => 2,
            'is_visible' => true,
            'settings' => [
                'limit' => 5,
                'timeframe' => 'month',
            ],
        ],
        [
            'name' => 'Recent Activity',
            'type' => 'recent_activity',
            'position' => 3,
            'is_visible' => true,
            'settings' => [
                'limit' => 10,
            ],
        ],
    ];

    /**
     * Get all available widget types with their configuration
     */
    public function getAvailableWidgetTypes(): array
    {
        return [
            'revenue_overview' => [
                'name' => 'Revenue Overview',
                'description' => 'Shows revenue trends over time',
                'settings' => [
                    'timeframe' => [
                        'type' => 'select',
                        'options' => [
                            'day' => 'Daily',
                            'week' => 'Weekly',
                            'month' => 'Monthly',
                            'year' => 'Yearly',
                        ],
                        'default' => 'month',
                    ],
                    'compare' => [
                        'type' => 'boolean',
                        'label' => 'Compare with previous period',
                        'default' => true,
                    ],
                ],
            ],
            'top_services' => [
                'name' => 'Top Services',
                'description' => 'Shows top performing services by revenue',
                'settings' => [
                    'limit' => [
                        'type' => 'number',
                        'min' => 1,
                        'max' => 10,
                        'default' => 5,
                    ],
                    'timeframe' => [
                        'type' => 'select',
                        'options' => [
                            'week' => 'This Week',
                            'month' => 'This Month',
                            'quarter' => 'This Quarter',
                            'year' => 'This Year',
                        ],
                        'default' => 'month',
                    ],
                ],
            ],
            'top_staff' => [
                'name' => 'Top Staff',
                'description' => 'Shows top performing staff by revenue',
                'settings' => [
                    'limit' => [
                        'type' => 'number',
                        'min' => 1,
                        'max' => 10,
                        'default' => 5,
                    ],
                    'timeframe' => [
                        'type' => 'select',
                        'options' => [
                            'week' => 'This Week',
                            'month' => 'This Month',
                            'quarter' => 'This Quarter',
                            'year' => 'This Year',
                        ],
                        'default' => 'month',
                    ],
                ],
            ],
            'recent_activity' => [
                'name' => 'Recent Activity',
                'description' => 'Shows recent system activities',
                'settings' => [
                    'limit' => [
                        'type' => 'number',
                        'min' => 1,
                        'max' => 20,
                        'default' => 10,
                    ],
                ],
            ],
        ];
    }

    /**
     * Get user's dashboard widgets
     */
    public function getUserWidgets(User $user): Collection
    {
        $widgets = $user->dashboardWidgets()
            ->orderBy('position')
            ->get();

        // If user has no widgets, create default ones
        if ($widgets->isEmpty()) {
            return $this->createDefaultWidgets($user);
        }

        return $widgets;
    }

    /**
     * Create default widgets for a user
     */
    protected function createDefaultWidgets(User $user): Collection
    {
        $widgets = collect();
        
        foreach ($this->defaultWidgets as $widgetData) {
            $widget = $user->dashboardWidgets()->create([
                'name' => $widgetData['name'],
                'type' => $widgetData['type'],
                'position' => $widgetData['position'],
                'is_visible' => $widgetData['is_visible'],
                'settings' => $widgetData['settings'] ?? [],
            ]);
            
            $widgets->push($widget);
        }
        
        return $widgets;
    }

    /**
     * Update user's dashboard layout
     */
    public function updateLayout(User $user, array $layout): void
    {
        $preferences = $this->getUserPreferences($user);
        $preferences->layout = $layout;
        $preferences->save();
    }

    /**
     * Update widget settings
     */
    public function updateWidget(User $user, int $widgetId, array $data): DashboardWidget
    {
        $widget = $user->dashboardWidgets()->findOrFail($widgetId);
        
        if (isset($data['position'])) {
            $widget->position = $data['position'];
        }
        
        if (isset($data['is_visible'])) {
            $widget->is_visible = $data['is_visible'];
        }
        
        if (isset($data['settings'])) {
            $widget->settings = array_merge((array) $widget->settings, $data['settings']);
        }
        
        $widget->save();
        
        return $widget;
    }

    /**
     * Add a new widget to user's dashboard
     */
    public function addWidget(User $user, string $type, array $settings = []): DashboardWidget
    {
        $availableWidgets = $this->getAvailableWidgetTypes();
        
        if (!isset($availableWidgets[$type])) {
            throw new \InvalidArgumentException("Invalid widget type: {$type}");
        }
        
        // Get the next available position
        $position = $user->dashboardWidgets()->max('position') + 1;
        
        return $user->dashboardWidgets()->create([
            'name' => $availableWidgets[$type]['name'],
            'type' => $type,
            'position' => $position,
            'is_visible' => true,
            'settings' => $settings,
        ]);
    }

    /**
     * Remove a widget from user's dashboard
     */
    public function removeWidget(User $user, int $widgetId): bool
    {
        return (bool) $user->dashboardWidgets()->where('id', $widgetId)->delete();
    }

    /**
     * Get or create user dashboard preferences
     */
    public function getUserPreferences(User $user): UserDashboardPreference
    {
        return $user->dashboardPreference ?? $user->dashboardPreference()->create([
            'layout' => [],
            'filters' => [],
            'theme' => 'light',
        ]);
    }

    /**
     * Update user dashboard preferences
     */
    public function updatePreferences(User $user, array $data): UserDashboardPreference
    {
        $preferences = $this->getUserPreferences($user);
        
        if (isset($data['layout'])) {
            $preferences->layout = $data['layout'];
        }
        
        if (isset($data['filters'])) {
            $preferences->filters = $data['filters'];
        }
        
        if (isset($data['theme'])) {
            $preferences->theme = $data['theme'];
        }
        
        $preferences->save();
        
        return $preferences;
    }
}
