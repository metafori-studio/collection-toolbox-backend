<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Metafori\Core\Filament\Forms\Components\PersonSelect;

class ResearchersSelect extends PersonSelect
{
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
