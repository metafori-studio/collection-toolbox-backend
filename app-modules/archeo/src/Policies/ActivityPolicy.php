<?php

namespace Metafori\Archeo\Policies;

use Metafori\Archeo\Models\Activity;
use Metafori\Core\Models\User;

class ActivityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['archeo_admin', 'archeo_readonly']);
    }

    public function view(User $user, Activity $activity): bool
    {
        return $user->hasRole(['archeo_admin', 'archeo_readonly']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('archeo_admin');
    }

    public function update(User $user, Activity $activity): bool
    {
        return $user->hasRole('archeo_admin');
    }

    public function delete(User $user, Activity $activity): bool
    {
        return $user->hasRole('archeo_admin');
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRole('archeo_admin');
    }

    public function restore(User $user, Activity $activity): bool
    {
        return $user->hasRole('archeo_admin');
    }

    public function forceDelete(User $user, Activity $activity): bool
    {
        return $user->hasRole('archeo_admin');
    }

    public function import(User $user): bool
    {
        return $user->hasRole('archeo_admin');
    }
}
