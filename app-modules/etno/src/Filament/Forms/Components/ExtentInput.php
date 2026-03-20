<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;

class ExtentInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Extent')
            ->requiredWith('extent_unit')
            ->maxLength(255);
    }
}
