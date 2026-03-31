<?php

namespace Metafori\Etno\Filament\Actions\Items;

use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use Metafori\Core\Filament\Forms\Components\FileUpload;
use Metafori\Etno\Filament\Concerns\HandlesMediaUploads;
use Metafori\Etno\Filament\Forms\Components\MediaFilesRepeater;
use Metafori\Etno\Jobs\Items\ProcessMediaUploadJob;
use Metafori\Etno\Repositories\ItemRepository;

class UploadMedia extends Action
{
    use HandlesMediaUploads;

    protected static function diskName(): ?string
    {
        return config('media-library.disk_name');
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->label('Upload Media')
            ->icon(Heroicon::OutlinedArrowUpTray)
            ->form([
                FileUpload::make('attachments')
                    ->disableLabel()
                    ->storeFiles(false)
                    ->previewable(false)
                    ->disk(self::diskName())
                    ->directory('etno/document-uploads')
                    ->multiple()
                    ->live()
                    ->afterStateUpdated(function (array $state, Set $set, Get $get, FileUpload $component) {
                        $items = $get->array('media_files');
                        $transcripts = $get->array('uploaded_transcripts');

                        [$mediaFiles, $transcripts] = $this->extractTranscript($state, $transcripts);
                        $set('uploaded_transcripts', $transcripts);

                        $assignedUuids = collect($items)->pluck('tmp_uuid')->filter(fn ($uuid) => $uuid !== null && $uuid !== '')->toArray();

                        $unassignedMapped = $this->mapUnassignedMediaFiles($mediaFiles, $assignedUuids, $component);

                        if (! empty($unassignedMapped)) {
                            $unassignedMapped = collect($unassignedMapped)
                                ->mapWithKeys(fn ($file) => [(string) Str::uuid() => $file])
                                ->toArray();

                            $items = [...$items, ...$unassignedMapped];
                        }

                        $items = $this->applyTranscriptsToFiles($items, $transcripts);

                        $set('media_files', $items);
                    }),

                Hidden::make('uploaded_transcripts')
                    ->default([]),

                MediaFilesRepeater::make('media_files'),
            ])
            ->action(function (array $data, RelationManager $livewire, ItemRepository $repository): void {
                /** @var \Metafori\Etno\Models\Item $owner */
                $owner = $livewire->getOwnerRecord();

                foreach ($data['media_files'] ?? [] as $media) {
                    ProcessMediaUploadJob::dispatch(
                        $owner,
                        $media['path'],
                        $media['client_original_name'],
                        self::diskName(),
                        customProperties: $media['custom_properties'] ?? [],
                        user: auth()->user(),
                    );

                    $repository->incrementPendingMediaUploads($owner);
                }

                Notification::make()
                    ->success()
                    ->title('Media processing started')
                    ->body('Uploaded files are being processed into media items.')
                    ->send();

                $livewire->dispatch('$refresh');
            });
    }
}
