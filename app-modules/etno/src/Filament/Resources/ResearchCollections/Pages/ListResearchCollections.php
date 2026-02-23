<?php

namespace Metafori\Etno\Filament\Resources\ResearchCollections\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Metafori\Etno\Filament\Resources\ResearchCollections\ResearchCollectionResource;

class ListResearchCollections extends ListRecords
{
    protected static string $resource = ResearchCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
