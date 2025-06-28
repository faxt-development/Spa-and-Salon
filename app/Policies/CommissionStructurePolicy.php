<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CommissionStructure;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommissionStructurePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_commission_structures');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CommissionStructure $commissionStructure): bool
    {
        return $user->can('view_commission_structures');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_commission_structures');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CommissionStructure $commissionStructure): bool
    {
        return $user->can('edit_commission_structures');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CommissionStructure $commissionStructure): bool
    {
        if ($commissionStructure->staff()->exists()) {
            return false;
        }
        
        return $user->can('delete_commission_structures');
    }
}
