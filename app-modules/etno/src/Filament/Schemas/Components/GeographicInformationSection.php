<?php

namespace Metafori\Etno\Filament\Schemas\Components;

use Filament\Schemas\Components\Section;
use Metafori\Etno\Filament\Forms\Components\LocalitiesRepeater;
use Metafori\Etno\Filament\Forms\Components\LocationNoteInput;

class GeographicInformationSection extends Section
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->heading('Geographic Information')
            ->schema([
                LocalitiesRepeater::make('localities'),
                LocationNoteInput::make('location_note')
                    ->translatableTabs(),
            ]);
    }
}
