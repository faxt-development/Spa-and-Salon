<?php

namespace App\Policies;

use App\Models\DashboardWidget;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DashboardWidgetPolicy
{
    /**
     * Determine whether the user can view any models.
     * Users can view their own widgets.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * Users can only view their own widgets.
     */
    public function view(User $user, DashboardWidget $dashboardWidget): bool
    {
        return $user->id === $dashboardWidget->user_id;
    }

    /**
     * Determine whether the user can create models.
     * Any authenticated user can create widgets for their own dashboard.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     * Users can only update their own widgets.
     */
    public function update(User $user, DashboardWidget $dashboardWidget): bool
    {
        return $user->id === $dashboardWidget->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     * Users can only delete their own widgets.
     */
    public function delete(User $user, DashboardWidget $dashboardWidget): bool
    {
        return $user->id === $dashboardWidget->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, DashboardWidget $dashboardWidget): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, DashboardWidget $dashboardWidget): bool
    {
        return false;
    }
}
