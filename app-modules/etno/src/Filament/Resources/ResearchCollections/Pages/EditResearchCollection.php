<?php

namespace Metafori\Etno\Filament\Resources\ResearchCollections\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Metafori\Etno\Filament\Resources\ResearchCollections\ResearchCollectionResource;

class EditResearchCollection extends EditRecord
{
    protected static string $resource = ResearchCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
