<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;

class DoiInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('DOI')
            ->placeholder('10.xxxx/xxxx')
            ->maxLength(255);
    }
}
