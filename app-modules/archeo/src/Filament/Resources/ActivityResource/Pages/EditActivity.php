<?php

namespace Metafori\Archeo\Filament\Resources\ActivityResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Metafori\Archeo\Filament\Resources\ActivityResource;

class EditActivity extends EditRecord
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
