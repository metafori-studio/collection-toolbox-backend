<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Metafori\Etno\Filament\Forms\Components\Concerns\CanBeInherited;

class GeneralNoteInput extends TextInput
{
    use CanBeInherited;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('General Note');
    }
}
