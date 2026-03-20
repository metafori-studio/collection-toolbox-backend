<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;

class IdInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('ID')
            ->required()
            ->maxLength(255)
            ->disabled(fn ($record) => $record !== null);
    }
}
