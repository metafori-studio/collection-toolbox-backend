<?php

namespace Metafori\Etno\Filament\Resources\Items\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Metafori\Etno\Enums\TranscriptFormat;
use Metafori\Etno\Filament\Resources\Items\Schemas\MediaForm;
use Metafori\Etno\Repositories\ItemRepository;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    public function form(Schema $schema): Schema
    {
        return MediaForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table
            ->poll(function (RelationManager $livewire, ItemRepository $itemRepository) {
                $count = $itemRepository->getProcessingMediaCount($livewire->getOwnerRecord());

                return $count > 0 ? '2s' : null;
            })
            ->description(function (RelationManager $livewire, ItemRepository $itemRepository) {
                $count = $itemRepository->getProcessingMediaCount($livewire->getOwnerRecord());

                return $count > 0
                    ? trans_choice('etno::ui.notifications.processing_media', $count)
                    : null;
            })
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mime_type')
                    ->limit(16)
                    ->tooltip(fn (Media $record): string => $record->mime_type)
                    ->label(__('etno::ui.fields.type'))
                    ->badge(),
                TextColumn::make('human_readable_size')
                    ->label(__('etno::ui.fields.size')),
                TextColumn::make('custom_properties.transcripts')
                    ->label(__('etno::ui.fields.transcripts'))
                    ->getStateUsing(fn (Media $record) => collect(TranscriptFormat::cases())
                        ->filter(fn (TranscriptFormat $format) => $record->getCustomProperty("transcripts.{$format->value}") !== null)
                    )
                    ->placeholder('–')
                    ->badge(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->reorderable('order_column')
            ->defaultSort('order_column');
    }
}
