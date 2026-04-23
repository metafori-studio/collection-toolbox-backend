<?php

namespace Metafori\Core\Filament\Resources\KeywordResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Metafori\Core\Filament\Resources\KeywordResource;

class ListKeywords extends ListRecords
{
    protected static string $resource = KeywordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
