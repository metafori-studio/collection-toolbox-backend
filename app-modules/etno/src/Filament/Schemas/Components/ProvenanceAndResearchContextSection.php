<?php

namespace Metafori\Etno\Filament\Schemas\Components;

use Filament\Schemas\Components\Section;
use Metafori\Etno\Filament\Forms\Components\AccrualMethodSelect;
use Metafori\Etno\Filament\Forms\Components\CollectionMethodSelect;
use Metafori\Etno\Filament\Forms\Components\InstitutionSelect;
use Metafori\Etno\Filament\Forms\Components\ProjectSelect;

class ProvenanceAndResearchContextSection extends Section
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->heading('Provenance and Research Context')
            ->schema([
                InstitutionSelect::make('institution_id')
                    ->columnSpanFull(),
                ProjectSelect::make('project_id')
                    ->columnSpanFull(),
                CollectionMethodSelect::make('collection_method'),
                AccrualMethodSelect::make('accrual_method'),
                TimePeriodSection::make(),
                SubmissionDateSection::make(),
                PublicationDateSection::make(),
            ])
            ->columns(2);
    }
}
