<?php

namespace Metafori\Core\Filament\Resources\DistrictResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Metafori\Core\Filament\Resources\DistrictResource;

class ManageDistricts extends ManageRecords
{
    protected static string $resource = DistrictResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
