<?php

namespace Metafori\Core\Filament\Resources\OrganizationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Metafori\Core\Filament\Resources\OrganizationResource;

class ListOrganizations extends ListRecords
{
    protected static string $resource = OrganizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
