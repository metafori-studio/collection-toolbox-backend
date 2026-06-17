<?php

namespace Metafori\Etno\Filament\Forms\Components;

use Filament\Schemas\Schema;
use Metafori\Core\Filament\Forms\Components\Select;
use Metafori\Etno\Filament\Forms\Components\Concerns\CanBeInherited;
use Metafori\Etno\Filament\Resources\ResearchCollections\Schemas\ResearchCollectionForm;

class ResearchCollectionSelect extends Select
{
    use CanBeInherited;

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('etno::ui.fields.research_collections'))
            ->relationship('researchCollections', 'title')
            ->multiple()
            ->searchable()
            ->reorderable()
            ->preload()
            ->createOptionForm(fn (Schema $schema) => ResearchCollectionForm::configure($schema))
            ->saveOrder();
    }
}
