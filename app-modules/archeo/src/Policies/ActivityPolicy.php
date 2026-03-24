<?php

namespace Metafori\Archeo\Policies;

use Metafori\Archeo\Models\Activity;
use Metafori\Core\Models\User;

class ActivityPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->hasRole(['admin', 'archeo_admin'])) {
            return true;
        }

        // Allow if user is assigned to at least one active activity
        return $user->activityAssignments()->where('expires_at', '>', now())->exists();
    }

    public function view(User $user, Activity $activity): bool
    {
        if ($user->hasRole(['admin', 'archeo_admin'])) {
            return true;
        }

        return $activity->isAssignedTo($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'archeo_admin']);
    }

    public function update(User $user, Activity $activity): bool
    {
        return $user->hasRole(['admin', 'archeo_admin']);
    }

    public function delete(User $user, Activity $activity): bool
    {
        return $user->hasRole(['admin', 'archeo_admin']);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole(['admin', 'archeo_admin']);
    }

    public function restore(User $user, Activity $activity): bool
    {
        return $user->hasRole(['admin', 'archeo_admin']);
    }

    public function forceDelete(User $user, Activity $activity): bool
    {
        return $user->hasRole(['admin', 'archeo_admin']);
    }

    public function import(User $user): bool
    {
        return $user->hasRole(['admin', 'archeo_admin']);
    }
}
