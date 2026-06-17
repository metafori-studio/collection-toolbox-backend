<?php

namespace Metafori\Etno\Filament\Schemas\Components;

class TimePeriodSection extends PrecisionDateSection
{
    protected function setUp(): void
    {
        $this->name('time_period')
            ->heading(__('etno::ui.sections.time_period'))
            ->rangeable();

        parent::setUp();
    }
}
