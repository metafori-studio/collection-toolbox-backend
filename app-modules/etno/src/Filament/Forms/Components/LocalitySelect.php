<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Metafori\Core\Filament\Forms\Components\LocalitySelect as CoreLocalitySelect;

class LocalitySelect extends CoreLocalitySelect
{
    public function setUp(): void
    {
        parent::setUp();

        $this->label('Locality')
            ->searchable()
            ->preload();
    }
}
