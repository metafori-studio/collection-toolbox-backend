<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Metafori\Core\Filament\Forms\Components\Select;
use Metafori\Etno\Enums\AccrualMethod;

class AccrualMethodSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Accrual Method')
            ->options(AccrualMethod::class)
            ->sortedOptions()
            ->searchable();
    }
}
