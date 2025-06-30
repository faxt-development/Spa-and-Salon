<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserDashboardPreference;
use Illuminate\Auth\Access\Response;

class UserDashboardPreferencePolicy
{
    /**
     * Determine whether the user can view any models.
     * Users can only view their own dashboard preferences.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     * Users can only view their own dashboard preferences.
     */
    public function view(User $user, UserDashboardPreference $userDashboardPreference): bool
    {
        return $user->id === $userDashboardPreference->user_id;
    }

    /**
     * Determine whether the user can create models.
     * Any authenticated user can create their own dashboard preferences.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     * Users can only update their own dashboard preferences.
     */
    public function update(User $user, UserDashboardPreference $userDashboardPreference): bool
    {
        return $user->id === $userDashboardPreference->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     * Users can only delete their own dashboard preferences.
     */
    public function delete(User $user, UserDashboardPreference $userDashboardPreference): bool
    {
        return $user->id === $userDashboardPreference->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, UserDashboardPreference $userDashboardPreference): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, UserDashboardPreference $userDashboardPreference): bool
    {
        return false;
    }
}
