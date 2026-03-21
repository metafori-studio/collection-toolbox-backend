<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Metafori\Core\Filament\Forms\Components\Select;
use Metafori\Etno\Enums\ProductionMethod;

class ProductionMethodsSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Production Methods')
            ->options(ProductionMethod::class)
            ->sortedOptions()
            ->multiple()
            ->reorderable()
            ->searchable();
    }
}
