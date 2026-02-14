<?php

namespace Metafori\Core\Filament\Resources\PersonResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Metafori\Core\Filament\Resources\PersonResource;

class ListPeople extends ListRecords
{
    protected static string $resource = PersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
