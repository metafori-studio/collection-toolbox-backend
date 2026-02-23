<?php

namespace Metafori\Core\Filament\Resources\LocalityResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Metafori\Core\Filament\Resources\LocalityResource;

class ListLocalities extends ListRecords
{
    protected static string $resource = LocalityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
