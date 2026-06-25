<?php

namespace Metafori\Etno\Filament\Resources\Documents\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Metafori\Etno\Filament\Resources\Documents\DocumentResource;
use Metafori\Etno\Support\Frontend;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_frontend')
                ->label(__('etno::ui.actions.view_frontend'))
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->url(fn (): string => Frontend::documentUrl($this->record->id))
                ->openUrlInNewTab()
                ->color('gray')
                ->visible(fn (): bool => $this->record->isPublished()),
            Action::make('unpublished')
                ->label(__('etno::ui.actions.unpublished'))
                ->color('gray')
                ->disabled()
                ->link()
                ->visible(fn (): bool => ! $this->record->isPublished()),
            $this->getSaveFormAction()
                ->formId('form'),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }
}
