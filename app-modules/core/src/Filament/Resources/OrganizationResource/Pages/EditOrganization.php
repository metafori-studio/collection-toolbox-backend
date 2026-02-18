<?php

namespace Metafori\Core\Filament\Resources\OrganizationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Metafori\Core\Filament\Resources\OrganizationResource;

class EditOrganization extends EditRecord
{
    protected static string $resource = OrganizationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
