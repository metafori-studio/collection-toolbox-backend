<?php

namespace Metafori\Etno\Filament\Schemas\Components;

use Filament\Schemas\Components\Section;
use Metafori\Etno\Filament\Forms\Components\ExtentsRepeater;
use Metafori\Etno\Filament\Forms\Components\ProductionMethodsSelect;
use Metafori\Etno\Filament\Forms\Components\TechnicalNoteInput;

class TechnicalAndFormatInformationSection extends Section
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->heading('Technical and Format Information')
            ->schema([
                ExtentsRepeater::make('extents')
                    ->columnSpanFull(),
                ProductionMethodsSelect::make('production_methods'),
                TechnicalNoteInput::make('technical_note')
                    ->translatableTabs(),
            ])
            ->columns(2);
    }
}
