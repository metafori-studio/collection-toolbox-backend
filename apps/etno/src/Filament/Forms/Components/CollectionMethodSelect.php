<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Metafori\Core\Filament\Forms\Components\Select;
use Metafori\Etno\Enums\CollectionMethod;
use Metafori\Etno\Filament\Forms\Components\Concerns\CanBeInherited;

class CollectionMethodSelect extends Select
{
    use CanBeInherited;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Collection Method')
            ->options(CollectionMethod::class)
            ->sortedOptions()
            ->searchable();
    }
}
