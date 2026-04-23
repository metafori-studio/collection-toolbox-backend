<?php

namespace Metafori\Etno\Policies;

use Metafori\Core\Models\User;
use Metafori\Etno\Enums\AccessRights;
use Metafori\Etno\Models\Item;

class ItemPolicy
{
    public function viewMedia(?User $user, Item $item): bool
    {
        $accessRights = $item->resolveInheritableAttribute('access_rights');

        return match ($accessRights) {
            AccessRights::OpenAccess => true,
            AccessRights::RestrictedAccess => $user !== null,
            default => false,
        };
    }
}
