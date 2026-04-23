<?php

namespace Metafori\Core\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Metafori\Core\Enums\Role as RoleEnum;
use Spatie\Permission\Models\Role as BaseRole;

class Role extends BaseRole
{
    public function enum(): Attribute
    {
        return Attribute::get(
            fn () => RoleEnum::from($this->name)
        );
    }

    public function label(): Attribute
    {
        return Attribute::get(
            fn () => $this->enum->getLabel()
        );
    }
}
