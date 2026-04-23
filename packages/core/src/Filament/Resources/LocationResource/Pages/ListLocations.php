<?php

namespace Metafori\Core\Filament\Resources\LocationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Metafori\Core\Filament\Resources\LocationResource;

class ListLocations extends ListRecords
{
    protected static string $resource = LocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
