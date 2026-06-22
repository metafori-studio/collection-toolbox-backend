<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Metafori\Core\Filament\Forms\Components\Select;
use Metafori\Etno\Enums\AccessRights;
use Metafori\Etno\Filament\Forms\Components\Concerns\CanBeInherited;

class AccessRightsSelect extends Select
{
    use CanBeInherited;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('etno::ui.fields.access_rights'))
            ->options(AccessRights::class)
            ->searchable();
    }
}
