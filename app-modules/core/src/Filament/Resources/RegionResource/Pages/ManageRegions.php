<?php

namespace Metafori\Core\Filament\Resources\RegionResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Metafori\Core\Filament\Resources\RegionResource;

class ManageRegions extends ManageRecords
{
    protected static string $resource = RegionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
