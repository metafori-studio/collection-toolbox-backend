<?php

namespace Metafori\Etno\Filament\Resources\Items\Pages;

use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Metafori\Etno\Filament\Actions\Items\UploadMediaAction;
use Metafori\Etno\Filament\Concerns\WithDocument;
use Metafori\Etno\Filament\Contracts\HasDocument;
use Metafori\Etno\Filament\Resources\Items\ItemResource;
use Metafori\Etno\Support\Frontend;

class EditItem extends EditRecord implements HasDocument
{
    use WithDocument;

    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_frontend')
                ->label(__('etno::ui.actions.view_frontend'))
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->url(fn (): string => Frontend::itemUrl($this->record->identifier))
                ->openUrlInNewTab()
                ->color('gray')
                ->visible(fn (): bool => $this->record->isPublished()),
            Action::make('unpublished')
                ->label(__('etno::ui.actions.unpublished'))
                ->color('gray')
                ->disabled()
                ->link()
                ->visible(fn (): bool => ! $this->record->isPublished()),
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
