<?php

namespace App\Policies;

use App\Models\User;
use Parallax\FilamentComments\Models\FilamentComment;

class FilamentCommentPolicy
{
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

        return $user->isAdmin();
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
