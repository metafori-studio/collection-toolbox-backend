<?php

namespace Metafori\Etno\Filament\Schemas\Components;

use Metafori\Core\Filament\Forms\Components\PrecisionDateSection;

class SubmissionDateSection extends PrecisionDateSection
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Submission')
            ->settingsField('submission_date_settings')
            ->startField('submission_date_start')
            ->endField('submission_date_end');
    }
}
