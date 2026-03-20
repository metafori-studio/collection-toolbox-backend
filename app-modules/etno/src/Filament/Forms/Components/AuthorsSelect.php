<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Metafori\Core\Filament\Forms\Components\PersonSelect;

class AuthorsSelect extends PersonSelect
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Authors')
            ->relationship('authors')
            ->multiple()
            ->searchable()
            ->reorderable()
            ->preload()
            ->withOptionForm()
            ->saveOrder();
    }
}
