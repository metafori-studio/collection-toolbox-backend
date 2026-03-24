<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Metafori\Core\Enums\License;
use Metafori\Core\Filament\Forms\Components\Select;
use Metafori\Etno\Filament\Forms\Components\Concerns\CanBeInherited;

class LicenseSelect extends Select
{
    use CanBeInherited;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('License')
            ->options(License::class)
            ->searchable();
    }
}
