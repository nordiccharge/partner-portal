<?php

namespace App\Policies;

use App\Models\User;
use Parallax\FilamentComments\Models\FilamentComment;

class FilamentCommentPolicy
{

    private function isAdmin(): bool {
        if (auth()->user()->isAdmin()) {
            return true;
        }

        return false;
    }
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FilamentComment $filamentComment): bool
    {
        return true;
    }

    public function create(User $user): bool
    {

        return $this->isAdmin() || auth()->user()->isTeamManager();
    }

    public function update(User $user, FilamentComment $filamentComment): bool
    {
        return false;
    }

    public function delete(User $user, FilamentComment $filamentComment): bool
    {
        return $user->isAdmin();
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }

    public function restore(User $user, FilamentComment $filamentComment): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, FilamentComment $filamentComment): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
