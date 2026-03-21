<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;

class GeneralNoteInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('General Note');
    }
}
