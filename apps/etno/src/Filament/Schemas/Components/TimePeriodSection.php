<?php

namespace Metafori\Etno\Filament\Schemas\Components;

class TimePeriodSection extends PrecisionDateSection
{
    protected function setUp(): void
    {
        $this->name('time_period')
            ->heading('Time Period')
            ->rangeable();

        parent::setUp();
    }
}
