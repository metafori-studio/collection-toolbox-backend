<?php

namespace Metafori\Etno\Filament\Schemas\Components;

use Filament\Schemas\Components\FusedGroup;
use Metafori\Etno\Filament\Forms\Components\ExtentInput;
use Metafori\Etno\Filament\Forms\Components\ExtentUnitSelect;

class ExtentGroup extends FusedGroup
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Extent')
            ->schema([
                ExtentInput::make('extent'),
                ExtentUnitSelect::make('extent_unit'),
            ])
            ->columns(2);
    }
}
