<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Metafori\Core\Filament\Forms\Components\Select;
use Metafori\Etno\Enums\ItemType;

class TypeSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Type')
            ->options(ItemType::class)
            ->sortedOptions()
            ->searchable();
    }
}
