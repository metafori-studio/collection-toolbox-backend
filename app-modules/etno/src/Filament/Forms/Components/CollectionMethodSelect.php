<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Metafori\Core\Filament\Forms\Components\Select;
use Metafori\Etno\Enums\CollectionMethod;

class CollectionMethodSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Collection Method')
            ->options(CollectionMethod::class)
            ->sortedOptions()
            ->searchable();
    }
}
