<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Metafori\Core\Filament\Forms\Components\PersonSelect;
use Metafori\Etno\Filament\Forms\Components\Concerns\CanBeInherited;

class ResearchersSelect extends PersonSelect
{
    use CanBeInherited;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Researchers')
            ->relationship('researchers')
            ->multiple()
            ->searchable()
            ->reorderable()
            ->preload()
            ->withOptionForm()
            ->saveOrder();
    }
}
