<?php

namespace Metafori\Etno\Filament\Schemas\Components;

class SubmissionDateSection extends PrecisionDateSection
{
    protected function setUp(): void
    {
        $this->name('submission_date')
            ->heading(__('etno::ui.sections.submission'));

        parent::setUp();
    }
}
