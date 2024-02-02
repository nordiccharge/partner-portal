<?php

namespace App\Policies;

use App\Models\Pipeline;
use App\Models\User;

class PipelinePolicy
{
    /**
     * Create a new policy instance.
     */
    private function isAdmin(): bool {
        if (auth()->user()->isAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view any models.
     */

    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Pipeline $pipeline): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->isAdmin() || auth()->user()->isTeamManager();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pipeline $pipeline): bool
    {
        return $this->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pipeline $pipeline): bool
    {
        return $this->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ReturnOrder $returnOrder): bool
    {
        return $this->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ReturnOrder $returnOrder): bool
    {
        return $this->isAdmin();
    }
}
