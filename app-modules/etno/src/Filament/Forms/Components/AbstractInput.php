<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Forms\Components\Textarea;
use Metafori\Etno\Filament\Forms\Components\Concerns\CanBeInherited;

class AbstractInput extends Textarea
{
    use CanBeInherited;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('etno::ui.fields.abstract'))
            ->rows(5);
    }
}
