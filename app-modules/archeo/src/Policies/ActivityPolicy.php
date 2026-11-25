<?php

namespace Metafori\Archeo\Policies;

use Metafori\Archeo\Enums\Permission;
use Metafori\Archeo\Models\Activity;
use Metafori\Core\Models\User;

class ActivityPolicy
{
    public function viewDocument(User $user, Activity $activity): bool
    {
        return $user->checkPermissionTo(Permission::ActivityViewAnyDocument)
            || $activity->isAssignedTo($user);
    }
}
