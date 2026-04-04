<?php

namespace Metafori\Etno\Filament\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Metafori\Etno\Enums\TranscriptFormat;

trait HandlesMediaUploads
{
    protected function extractTranscripts(array $files, array $transcripts): array
    {
        [$transcriptFiles, $mediaFiles] = collect($files)
            ->partition($this->isTranscript(...))
            ->all();

        foreach ($transcriptFiles as $file) {
            $format = $this->getTranscriptFormat($file);
            $basename = File::name($file->getClientOriginalName());
            $transcripts[$basename][$format->value] ??= $file->get();
        }

        return [$transcripts, $mediaFiles];
    }

    protected function syncMedia(Collection $files, Collection $media): void
    {
        $removedKeys = $media
            ->reject(fn ($medium) => $files->contains($medium['file']))
            ->keys();

        $media->forget($removedKeys);

        $files
            ->reject(fn ($file) => $media->contains('file', $file))
            ->each(fn ($file) => $media->push([
                'file' => $file,
                'custom_properties' => [],
            ]));
    }

    protected function getTranscriptFormat(TemporaryUploadedFile $file): ?TranscriptFormat
    {
        $type = strtolower($file->getClientOriginalExtension());

        return TranscriptFormat::tryFrom($type);
    }

    protected function isTranscript(TemporaryUploadedFile $file): bool
    {
        return $this->getTranscriptFormat($file) !== null;
    }

    protected function applyTranscriptsToMedia(Collection $media, array $transcripts): void
    {
        $media
            ->transform(function ($medium) use ($transcripts) {
                $file = $medium['file'];
                $basename = File::name($file->getClientOriginalName());

                foreach (TranscriptFormat::cases() as $format) {
                    if (isset($transcripts[$basename][$format->value])) {
                        $medium['custom_properties']['transcripts'][$format->value] ??= $transcripts[$basename][$format->value];
                    }
                }

                return $medium;
            });
    }
}
