<?php

namespace Metafori\Etno\Filament\Schemas\Components;

class SubmissionDateSection extends PrecisionDateSection
{
    protected function setUp(): void
    {
        $this->name('submission_date')
            ->heading('Submission');

        parent::setUp();
    }
}
