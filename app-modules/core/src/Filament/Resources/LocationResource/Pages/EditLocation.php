<?php

namespace Metafori\Core\Filament\Resources\LocationResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Metafori\Core\Filament\Resources\LocationResource;

class EditLocation extends EditRecord
{
    protected static string $resource = LocationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
