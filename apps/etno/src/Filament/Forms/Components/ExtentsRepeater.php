<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\FusedGroup;
use Metafori\Etno\Filament\Forms\Components\Concerns\CanBeInherited;

class ExtentsRepeater extends Repeater
{
    use CanBeInherited;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Extents')
            ->reorderableWithButtons()
            ->defaultItems(0)
            ->schema([
                FusedGroup::make()
                    ->schema([
                        ExtentInput::make('value')
                            ->required(),
                        ExtentUnitSelect::make('unit')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
