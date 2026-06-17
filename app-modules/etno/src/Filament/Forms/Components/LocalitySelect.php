<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Metafori\Core\Filament\Forms\Components\LocalitySelect as CoreLocalitySelect;
use Metafori\Etno\Filament\Forms\Components\Concerns\CanBeInherited;

class LocalitySelect extends CoreLocalitySelect
{
    use CanBeInherited;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('etno::ui.fields.locality'))
            ->searchable()
            ->preload();
    }
}
