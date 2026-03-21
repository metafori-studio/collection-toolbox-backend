<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;

class SubtitleInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Subtitle');
    }
}
