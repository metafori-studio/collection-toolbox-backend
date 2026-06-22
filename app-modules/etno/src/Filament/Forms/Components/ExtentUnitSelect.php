<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Metafori\Core\Filament\Forms\Components\Select;
use Metafori\Etno\Enums\ExtentUnit;

class ExtentUnitSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('etno::ui.fields.extent_unit'))
            ->options(ExtentUnit::class)
            ->sortedOptions()
            ->searchable();
    }
}
