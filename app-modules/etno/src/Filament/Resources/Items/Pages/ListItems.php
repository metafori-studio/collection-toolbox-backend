<?php

namespace Metafori\Etno\Filament\Resources\Items\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Metafori\Etno\Filament\Resources\Items\ItemResource;

class ListItems extends ListRecords
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
