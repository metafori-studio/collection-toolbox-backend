<?php

namespace Metafori\Etno\Filament\Concerns;

use Illuminate\Support\Facades\File;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Metafori\Etno\Enums\TranscriptFormat;

trait HandlesMediaUploads
{
    protected function extractTranscript(array $state, array $transcripts = []): array
    {
        $mediaFiles = [];

        foreach ($state as $file) {
            if (! ($file instanceof TemporaryUploadedFile)) {
                continue;
            }

            $basename = File::name($file->getClientOriginalName());

            $transcript = $this->getTranscriptFormat($file);

            if ($transcript) {
                $transcripts[$basename][$transcript->value] = $file->get();

                continue;
            }

            $mediaFiles[$file->getFilename()] = $file;
        }

        return [$mediaFiles, $transcripts];
    }

    protected function getTranscriptFormat(TemporaryUploadedFile $file): ?TranscriptFormat
    {
        $type = strtolower($file->getClientOriginalExtension());

        return TranscriptFormat::tryFrom($type);
    }

    protected function mapUnassignedMediaFiles(array $mediaFiles, array $assignedUuids): array
    {
        return collect($mediaFiles)
            ->reject(fn ($file, $uuid) => \in_array($uuid, $assignedUuids))
            ->map(function (TemporaryUploadedFile $file, string $uuid) {
                $filename = $file->getClientOriginalName();

                return [
                    'tmp_uuid' => $uuid,
                    'client_original_name' => $filename,
                    'basename' => File::name($filename),
                    'mime_type' => $file->getMimeType(),
                    'path' => $file->getPath(),
                    'custom_properties' => [],
                ];
            })
            ->toArray();
    }

    protected function applyTranscriptsToFiles(array $files, array $transcripts): array
    {
        return collect($files)->map(function (array $fileRow) use ($transcripts) {
            $basename = $fileRow['basename'] ?? null;

            if ($basename) {
                foreach (TranscriptFormat::cases() as $format) {
                    if (isset($transcripts[$basename][$format->value])) {
                        $fileRow['custom_properties'][$format->getCustomPropertyKey()] = $transcripts[$basename][$format->value];
                    }
                }
            }

            return $fileRow;
        })->toArray();
    }

    protected function applyTranscriptsToItemGroups(array $itemGroups, array $transcripts): array
    {
        return collect($itemGroups)->map(fn (array $itemRow) => [
            ...$itemRow,
            'media_files' => $this->applyTranscriptsToFiles($itemRow['media_files'] ?? [], $transcripts),
        ])->toArray();
    }
}
