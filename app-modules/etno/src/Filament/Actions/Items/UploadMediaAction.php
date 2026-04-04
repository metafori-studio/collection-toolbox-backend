<?php

namespace Metafori\Etno\Filament\Actions\Items;

use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\File;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
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
                    ->disableLabel()
                    ->storeFiles(false)
                    ->previewable(false)
                    ->multiple()
                    ->live()
                    ->afterStateUpdated(function (array $state, Set $set, Get $get) {
                        $media = collect($get->array('media'));
                        $transcripts = $get->array('transcripts');

                        [$transcripts, $mediaFiles] = $this->extractTranscripts($state, $transcripts);

                        $this->syncMedia($mediaFiles, $media);
                        $this->applyTranscriptsToMedia($media, $transcripts);

                        $set('transcripts', $transcripts);
                        $set('media', $media->toArray());
                    }),

                Hidden::make('transcripts')
                    ->default([]),

                MediaRepeater::make('media')
                    ->rule(fn (Item $record) => function (string $attribute, $value, Closure $fail) use ($record) {
                        $mimeTypes = $record->getMedia()
                            ->pluck('mime_type')
                            ->merge(collect($value)->pluck('file')->map->getMimeType())
                            ->unique();

                        if ($mimeTypes->count() > 1) {
                            $fail("The mime type of the file must match the other item's media files.");
                        }
                    }),
            ])
            ->action(function (array $data, Item $record): void {
                foreach ($data['media'] ?? [] as $media) {
                    $record->addMediaFromDisk($media['file']->getClientOriginalPath(), FileUploadConfiguration::disk())
                        ->usingName(File::name($media['file']->getClientOriginalName()))
                        ->usingFileName($media['file']->getClientOriginalName())
                        ->withCustomProperties($media['custom_properties'] ?? [])
                        ->toMediaCollection();
                }

                $this->redirect(ItemResource::getUrl('edit', [
                    'document' => $record->document,
                    'record' => $record->id,
                    'relation' => 'media',
                ]));
            });
    }
}
