<?php

namespace Metafori\Etno\Filament\Schemas\Components;

use Metafori\Core\Filament\Forms\Components\PrecisionDateSection;

class PublicationDateSection extends PrecisionDateSection
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Publication')
            ->settingsField('publication_date_settings')
            ->startField('publication_date_start')
            ->endField('publication_date_end');
    }
}
