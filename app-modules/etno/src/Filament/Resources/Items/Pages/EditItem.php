<?php

namespace Metafori\Etno\Filament\Resources\Items\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Metafori\Etno\Filament\Resources\Items\ItemResource;

class EditItem extends EditRecord
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->formId('form'),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
