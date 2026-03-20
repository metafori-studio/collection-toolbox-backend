<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;

class TitleInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Title');
    }
}
