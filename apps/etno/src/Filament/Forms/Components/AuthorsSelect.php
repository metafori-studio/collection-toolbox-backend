<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Metafori\Core\Filament\Forms\Components\PersonSelect;
use Metafori\Etno\Filament\Forms\Components\Concerns\CanBeInherited;

class AuthorsSelect extends PersonSelect
{
    use CanBeInherited;

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
