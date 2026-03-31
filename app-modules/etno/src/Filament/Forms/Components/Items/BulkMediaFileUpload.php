<?php

namespace Metafori\Etno\Filament\Forms\Components\Items;

use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Metafori\Core\Filament\Forms\Components\FileUpload;
use Metafori\Etno\Filament\Actions\Items\RegenerateIds;
use Metafori\Etno\Filament\Concerns\HandlesMediaUploads;

class BulkMediaFileUpload extends FileUpload
{
    use HandlesMediaUploads;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storeFiles(false)
            ->previewable(false)
            ->multiple()
            ->hiddenLabel()
            ->disk(config('media-library.disk_name'))
            ->directory('document-uploads')
            ->live()
            ->afterStateUpdated(function (Set $set, Get $get, $state, \Livewire\Component $livewire) {
                /** @var \Metafori\Etno\Filament\Resources\Items\Pages\CreateFromFiles $livewire */
                $items = $get->array('items');
                $transcripts = $get->array('uploaded_transcripts');
                $document = $livewire->getParentRecord();
                $strategy = $get('grouping_strategy');

                // Extract transcripts and separate media files
                [$mediaFiles, $transcripts] = $this->extractTranscript($state, $transcripts);
                $set('uploaded_transcripts', $transcripts);

                $assignedUuids = collect($items)
                    ->flatMap(fn ($item) => collect($item['media_files'] ?? [])->pluck('tmp_uuid'))
                    ->filter(fn ($uuid) => $uuid !== null && $uuid !== '')
                    ->unique()
                    ->toArray();

                // Map new, unassigned media files
                $unassignedMapped = $this->mapUnassignedMediaFiles($mediaFiles, $assignedUuids);

                // Group new files and merge into Items repeater
                if (! empty($unassignedMapped)) {
                    $suffix = RegenerateIds::getNextSequenceSuffix($document, $items);
                    $items = $livewire->groupFiles($unassignedMapped, $strategy, $suffix, $items);
                }

                // Apply any available transcripts
                $items = $this->applyTranscriptsToItemGroups($items, $transcripts);

                $set('items', $items);
            });
    }
}
