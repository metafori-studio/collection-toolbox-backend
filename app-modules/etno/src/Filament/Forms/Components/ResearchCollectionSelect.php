<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Schemas\Schema;
use Metafori\Core\Filament\Forms\Components\Select;
use Metafori\Etno\Filament\Resources\ResearchCollections\Schemas\ResearchCollectionForm;

class ResearchCollectionSelect extends Select
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Research Collections')
            ->relationship('researchCollections', 'title')
            ->multiple()
            ->searchable()
            ->reorderable()
            ->preload()
            ->createOptionForm(fn (Schema $schema) => ResearchCollectionForm::configure($schema))
            ->saveOrder();
    }
}
