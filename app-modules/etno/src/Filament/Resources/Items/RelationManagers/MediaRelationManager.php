<?php

namespace Metafori\Etno\Filament\Resources\Items\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Metafori\Etno\Enums\TranscriptFormat;
use Metafori\Etno\Filament\Actions\Items\UploadMedia;
use Metafori\Etno\Filament\Actions\Items\ViewMediaInFrontend;
use Metafori\Etno\Repositories\ItemRepository;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaRelationManager extends RelationManager
{
    protected static string $relationship = 'media';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Schema $schema): Schema
    {
        $transcriptFields = [];
        foreach (TranscriptFormat::cases() as $format) {
            $transcriptFields[] = Textarea::make("custom_properties.{$format->getCustomPropertyKey()}")
                ->label("Transcript ({$format->getLabel()})")
                ->rows(8);
        }

        return $schema->components([
            Actions::make([ViewMediaInFrontend::make()])
                ->columnSpanFull()
                ->hidden(fn (?Media $record): bool => $record === null),
            ...$transcriptFields,
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->poll()
            ->description(function (RelationManager $livewire, ItemRepository $repository) {
                $pendingJobs = $repository->getPendingMediaUploadsCount($livewire->getOwnerRecord());

                return $pendingJobs > 0 ? trans_choice('etno::messages.pending_media_jobs', $pendingJobs) : null;
            })
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mime_type')
                    ->limit(16)
                    ->tooltip(fn (Media $record): string => $record->mime_type)
                    ->label('Type')
                    ->badge(),
                TextColumn::make('human_readable_size')
                    ->label('Size'),
                TextColumn::make('custom_properties.transcript')
                    ->label('Transcript')
                    ->getStateUsing(function (Media $record) {
                        $badges = [];

                        foreach (TranscriptFormat::cases() as $format) {
                            if (! empty($record->custom_properties[$format->getCustomPropertyKey()])) {
                                $badges[] = $format->getLabel();
                            }
                        }

                        return \count($badges) > 0 ? $badges : ['None'];
                    })
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'None' => Color::Gray,
                        default => null,
                    }),
            ])
            ->headerActions([
                UploadMedia::make('upload_media'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->reorderable('order_column')
            ->defaultSort('order_column');
    }
}
