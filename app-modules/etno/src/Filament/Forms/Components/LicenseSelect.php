<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Metafori\Core\Enums\License;
use Metafori\Core\Filament\Forms\Components\Select;

class LicenseSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('License')
            ->options(License::class)
            ->searchable();
    }
}
