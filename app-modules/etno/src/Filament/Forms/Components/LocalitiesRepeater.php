<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Forms\Components\Repeater;
use Metafori\Core\Filament\Forms\Components\LocalitySelect;

class LocalitiesRepeater extends Repeater
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Localities')
            ->relationship('localities')
            ->schema([
                LocalitySelect::make('locality')
                    ->required(),
            ])
            ->defaultItems(0)
            ->reorderableWithButtons()
            ->orderColumn('sort_order');
    }
}
