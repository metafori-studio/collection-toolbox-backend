<?php

namespace Metafori\Etno\Filament\Resources\Items\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Metafori\Etno\Filament\Actions\Items\UploadMediaAction;
use Metafori\Etno\Filament\Concerns\WithDocument;
use Metafori\Etno\Filament\Contracts\HasDocument;
use Metafori\Etno\Filament\Resources\Items\ItemResource;

class EditItem extends EditRecord implements HasDocument
{
    use WithDocument;

    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            UploadMediaAction::make('upload_media'),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
