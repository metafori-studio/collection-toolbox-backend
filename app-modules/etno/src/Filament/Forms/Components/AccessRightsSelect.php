<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Metafori\Core\Filament\Forms\Components\Select;
use Metafori\Etno\Enums\AccessRights;

class AccessRightsSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Access Rights')
            ->options(AccessRights::class)
            ->searchable();
    }
}
