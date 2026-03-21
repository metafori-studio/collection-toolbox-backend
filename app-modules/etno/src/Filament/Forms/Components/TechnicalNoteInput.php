<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;

class TechnicalNoteInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Technical Note');
    }
}
