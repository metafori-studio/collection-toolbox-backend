<?php

namespace Metafori\Etno\Filament\Resources\Documents\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Metafori\Etno\Filament\Resources\Documents\DocumentResource;

class ListDocuments extends ListRecords
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
