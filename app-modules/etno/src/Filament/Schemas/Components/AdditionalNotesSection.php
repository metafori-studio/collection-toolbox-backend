<?php

namespace Metafori\Etno\Filament\Schemas\Components;

use Filament\Schemas\Components\Section;
use Metafori\Etno\Filament\Forms\Components\GeneralNoteInput;

class AdditionalNotesSection extends Section
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->heading('Additional Notes')
            ->schema([
                GeneralNoteInput::make('general_note')
                    ->translatableTabs()
                    ->columnSpanFull(),
            ]);
    }
}
