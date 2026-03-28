<?php

namespace Metafori\Etno\Filament\Resources\Items\Pages;

use Filament\Resources\Pages\CreateRecord;
use Metafori\Etno\Filament\Resources\Items\ItemResource;

class CreateItem extends CreateRecord
{
    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getCreateFormAction()
                ->formId('form'),
            $this->getCreateAnotherFormAction()
                ->formId('form'),
        ];
    }
}
