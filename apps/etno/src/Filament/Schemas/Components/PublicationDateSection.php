<?php

namespace Metafori\Etno\Filament\Schemas\Components;

class PublicationDateSection extends PrecisionDateSection
{
    protected function setUp(): void
    {
        $this->name('publication_date')
            ->heading('Publication');

        parent::setUp();
    }
}
