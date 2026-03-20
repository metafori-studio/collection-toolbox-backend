<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Forms\Components\Textarea;

class AbstractInput extends Textarea
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Abstract')
            ->rows(5);
    }
}
