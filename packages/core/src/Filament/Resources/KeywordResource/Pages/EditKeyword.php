<?php

namespace Metafori\Core\Filament\Resources\KeywordResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Metafori\Core\Filament\Resources\KeywordResource;

class EditKeyword extends EditRecord
{
    protected static string $resource = KeywordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
