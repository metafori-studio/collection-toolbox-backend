<?php

namespace Metafori\Etno\Filament\Resources\Projects\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Metafori\Etno\Filament\Resources\Projects\ProjectResource;

class EditProject extends EditRecord
{
    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
