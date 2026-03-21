<?php

namespace Metafori\Etno\Filament\Schemas\Components;

use Metafori\Core\Filament\Forms\Components\PrecisionDateSection;

class TimePeriodSection extends PrecisionDateSection
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Time period')
            ->settingsField('time_period_settings')
            ->startField('time_period_start')
            ->endField('time_period_end')
            ->rangeable();
    }
}
