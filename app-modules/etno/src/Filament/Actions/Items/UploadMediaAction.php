<?php

namespace Metafori\Etno\Filament\Actions\Items;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Metafori\Etno\Enums\TranscriptFormat;
use Metafori\Etno\Filament\Concerns\HandlesMediaUploads;
use Metafori\Etno\Filament\Forms\Components\Items\MediaRepeater;
use Metafori\Etno\Filament\Resources\Items\ItemResource;
use Metafori\Etno\Models\Item;

class UploadMediaAction extends Action
{
    use HandlesMediaUploads;

    public function setUp(): void
    {
        parent::setUp();

        $this->label('Upload Media')
            ->icon(Heroicon::OutlinedArrowUpTray)
            ->form([
                FileUpload::make('files')
                    ->maxParallelUploads(6)
                    ->hiddenLabel()
                    ->storeFiles(false)
                    ->previewable(false)
                    ->multiple()
                    ->live()
                    ->acceptedFileTypes(fn (Item $record) => [
                        ...$record->allowedMediaMimeTypes(),
                        ...TranscriptFormat::mimeTypes(),
                    ])
                    ->afterStateUpdated(function (array $state, Set $set, Get $get) {
                        $media = $get->array('media');
                        $transcripts = $get->array('transcripts');

                        [$transcripts, $mediaFiles] = $this->extractTranscripts($state, $transcripts);

                        $media = $this->syncMedia($mediaFiles, $media);
                        $media = $this->applyTranscriptsToMedia($media, $transcripts);

                        $set('transcripts', $transcripts);
                        $set('media', $media);
                    })
                    ->rule(fn (Get $get) => function (string $attribute, $value, Closure $fail) use ($get) {
                        $files = $get->array('files') ?? [];

                        [$transcripts, $mediaFiles] = collect($files)
                            ->partition($this->isTranscript(...))
                            ->all();

                        if ($mediaFiles->isEmpty() && $transcripts->isNotEmpty()) {
                            $fail('Cannot upload transcripts without corresponding media files.');
                        }
                    }),

                Hidden::make('transcripts')
                    ->default([]),

                MediaRepeater::make('media')
                    ->rule(fn (Item $record) => function (string $attribute, $value, Closure $fail) use ($record) {
                        $mediaTypes = $record->media
                            ->pluck('mime_type')
                            ->merge(collect($value)->pluck('file')->map->getMimeType())
                            ->map(Item::getMediaCollectionName(...))
                            ->unique();

                        if ($mediaTypes->count() > 1) {
                            $fail("The media type of the file must match the other item's media files.");
                        }
                    }),
            ])
            ->action(function (array $data, Item $record): void {
                foreach ($data['media'] ?? [] as $media) {
                    $this->addItemMedia($record, $media['file'], $media['custom_properties']);
                }

                $this->redirect(ItemResource::getUrl('edit', [
                    'document' => $record->document,
                    'record' => $record->id,
                    'relation' => 'media',
                ]));
            });
    }
}
