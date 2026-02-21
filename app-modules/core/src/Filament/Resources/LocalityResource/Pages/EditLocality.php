<?php

namespace Metafori\Core\Filament\Resources\LocalityResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Metafori\Core\Filament\Resources\LocalityResource;

class EditLocality extends EditRecord
{
    protected static string $resource = LocalityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
