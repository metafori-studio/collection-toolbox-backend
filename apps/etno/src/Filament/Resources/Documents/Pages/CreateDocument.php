<?php

namespace Metafori\Etno\Filament\Resources\Documents\Pages;

use Filament\Resources\Pages\CreateRecord;
use Metafori\Etno\Filament\Resources\Documents\DocumentResource;

class CreateDocument extends CreateRecord
{
    protected static string $resource = DocumentResource::class;

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
