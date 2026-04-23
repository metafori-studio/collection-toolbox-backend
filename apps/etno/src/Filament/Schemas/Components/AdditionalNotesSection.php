<?php

namespace Metafori\Etno\Filament\Schemas\Components;

use Filament\Schemas\Components\Section;
use Metafori\Etno\Filament\Forms\Components\GeneralNoteInput;
use Metafori\Etno\Filament\Schemas\Components\Concerns\HasInheritable;

class AdditionalNotesSection extends Section
{
    use HasInheritable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->heading('Additional Notes')
            ->schema(fn () => [
                GeneralNoteInput::make('general_note')
                    ->inheritable($this->inheritable)
                    ->translatableTabs()
                    ->columnSpanFull(),
            ])
            ->collapsible();
    }
}
