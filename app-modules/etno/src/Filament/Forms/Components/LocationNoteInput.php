<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;

class LocationNoteInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Location Note');
    }
}
