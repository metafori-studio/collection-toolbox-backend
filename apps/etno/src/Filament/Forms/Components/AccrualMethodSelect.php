<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Metafori\Core\Filament\Forms\Components\Select;
use Metafori\Etno\Enums\AccrualMethod;
use Metafori\Etno\Filament\Forms\Components\Concerns\CanBeInherited;

class AccrualMethodSelect extends Select
{
    use CanBeInherited;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Accrual Method')
            ->options(AccrualMethod::class)
            ->sortedOptions()
            ->searchable();
    }
}
