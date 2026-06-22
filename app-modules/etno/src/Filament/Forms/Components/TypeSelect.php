<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Metafori\Core\Filament\Forms\Components\Select;
use Metafori\Etno\Enums\ItemType;
use Metafori\Etno\Filament\Forms\Components\Concerns\CanBeInherited;

class TypeSelect extends Select
{
    use CanBeInherited;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('etno::ui.fields.type'))
            ->options(ItemType::class)
            ->sortedOptions()
            ->searchable();
    }
}
