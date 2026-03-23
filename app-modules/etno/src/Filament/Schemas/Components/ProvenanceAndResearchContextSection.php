<?php

namespace Metafori\Etno\Filament\Schemas\Components;

use Filament\Schemas\Components\Section;
use Metafori\Etno\Filament\Forms\Components\AccrualMethodSelect;
use Metafori\Etno\Filament\Forms\Components\CollectionMethodSelect;
use Metafori\Etno\Filament\Forms\Components\InstitutionSelect;
use Metafori\Etno\Filament\Forms\Components\ProjectSelect;
use Metafori\Etno\Filament\Schemas\Components\Concerns\HasInheritable;

class ProvenanceAndResearchContextSection extends Section
{
    use HasInheritable;

    protected function setUp(): void
    {
        parent::setUp();

        $this->heading('Provenance and Research Context')
            ->schema(fn () => [
                InstitutionSelect::make('institution_id')
                    ->inheritable($this->inheritable)
                    ->columnSpanFull(),
                ProjectSelect::make('project_id')
                    ->inheritable($this->inheritable)
                    ->columnSpanFull(),
                CollectionMethodSelect::make('collection_method')
                    ->inheritable($this->inheritable),
                AccrualMethodSelect::make('accrual_method')
                    ->inheritable($this->inheritable),
                SubmissionDateSection::make(),
                PublicationDateSection::make(),
                TimePeriodSection::make()
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }
}
