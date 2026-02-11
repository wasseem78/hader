<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BranchPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any branches.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the branch.
     */
    public function view(User $user, Branch $branch): bool
    {
        return $user->company_id === $branch->company_id;
    }

    /**
     * Determine whether the user can create branches.
     */
    public function create(User $user): bool
    {
        return true; // Any authenticated user can create branches for their company
    }

    /**
     * Determine whether the user can update the branch.
     */
    public function update(User $user, Branch $branch): bool
    {
        return $user->company_id === $branch->company_id;
    }

    /**
     * Determine whether the user can delete the branch.
     */
    public function delete(User $user, Branch $branch): bool
    {
        return $user->company_id === $branch->company_id;
    }
}
