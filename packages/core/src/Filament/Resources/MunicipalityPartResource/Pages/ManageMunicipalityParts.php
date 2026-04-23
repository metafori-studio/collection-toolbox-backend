<?php

namespace Metafori\Core\Filament\Resources\MunicipalityPartResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Metafori\Core\Filament\Resources\MunicipalityPartResource;

class ManageMunicipalityParts extends ManageRecords
{
    protected static string $resource = MunicipalityPartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
