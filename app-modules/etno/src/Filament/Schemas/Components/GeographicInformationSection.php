<?php

namespace Metafori\Etno\Filament\Schemas\Components;

use Filament\Schemas\Components\Section;
use Metafori\Etno\Filament\Forms\Components\LocalitySelect;
use Metafori\Etno\Filament\Forms\Components\LocationNoteInput;

class GeographicInformationSection extends Section
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->heading('Geographic Information')
            ->schema([
                LocalitySelect::make('locality'),
                LocationNoteInput::make('location_note')
                    ->translatableTabs(),
            ]);
    }
}
