<?php

namespace Metafori\Archeo\Policies;

use Metafori\Archeo\Models\Activity;
use Metafori\Core\Enums\Role;
use Metafori\Core\Models\User;

class ActivityPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole(Role::Admin)) {
            return true;
        }
    }

    public function viewDocument(User $user, Activity $activity): bool
    {
        if ($user->hasRole(Role::Admin)) {
            return true;
        }

        return $activity->isAssignedTo($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(Role::Admin);
    }

    public function update(User $user, Activity $activity): bool
    {
        return $user->hasRole(Role::Admin);
    }

    public function delete(User $user, Activity $activity): bool
    {
        return $user->hasRole(Role::Admin);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(Role::Admin);
    }

    public function restore(User $user, Activity $activity): bool
    {
        return $user->hasRole(Role::Admin);
    }

    public function forceDelete(User $user, Activity $activity): bool
    {
        return $user->hasRole(Role::Admin);
    }

    public function import(User $user): bool
    {
        return $user->hasRole(Role::Admin);
    }
}
