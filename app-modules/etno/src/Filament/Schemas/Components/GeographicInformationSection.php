<?php

namespace Metafori\Etno\Filament\Schemas\Components;

use Filament\Schemas\Components\Section;
use Metafori\Etno\Filament\Forms\Components\LocalitySelect;
use Metafori\Etno\Filament\Forms\Components\LocationNoteInput;
use Metafori\Etno\Filament\Schemas\Components\Concerns\HasInheritable;

class GeographicInformationSection extends Section
{
    use HasInheritable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->heading(__('etno::ui.sections.geographic_information'))
            ->schema(fn () => [
                LocalitySelect::make('locality')
                    ->includeLocation()
                    ->inheritable($this->inheritable),
                LocationNoteInput::make('location_note')
                    ->inheritable($this->inheritable)
                    ->translatableTabs(),
            ])
            ->collapsible();
    }
}
