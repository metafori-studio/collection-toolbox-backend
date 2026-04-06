<?php

namespace Metafori\Etno\Filament\Concerns;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Metafori\Etno\Enums\TranscriptFormat;
use Metafori\Etno\Models\Item;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HandlesMediaUploads
{
    protected function extractTranscripts(array $files, array $transcripts = []): array
    {
        [$transcriptFiles, $mediaFiles] = collect($files)
            ->partition($this->isTranscript(...))
            ->all();

        foreach ($transcriptFiles as $file) {
            $format = $this->getTranscriptFormat($file);
            $basename = File::name($file->getClientOriginalName());
            $transcripts[$basename][$format->value] ??= $file->get();
        }

        return [$transcripts, $mediaFiles->toArray()];
    }

    protected function syncMedia(array $files, array $media): array
    {
        $media = array_filter($media, fn ($medium) => \in_array($medium['file'], $files, strict: false));

        $mediaFiles = array_column($media, 'file');
        foreach ($files as $file) {
            if (! \in_array($file, $mediaFiles, strict: false)) {
                $media[(string) Str::uuid()] = [
                    'file' => $file,
                    'custom_properties' => [],
                ];
            }
        }

        return $media;
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

    protected function applyTranscriptsToMedium(array $medium, array $transcripts): array
    {
        $basename = File::name($medium['file']->getClientOriginalName());

        foreach (TranscriptFormat::cases() as $format) {
            if (isset($transcripts[$basename][$format->value])) {
                $medium['custom_properties']['transcripts'][$format->value] ??= $transcripts[$basename][$format->value];
            }
        }

        return $medium;
    }

    protected function applyTranscriptsToMedia(array $media, array $transcripts): array
    {
        foreach ($media as &$medium) {
            $medium = $this->applyTranscriptsToMedium($medium, $transcripts);
        }

        return $media;
    }

    protected function addItemMedia(Item $item, TemporaryUploadedFile $file, array $customProperties): Media
    {
        return $item->addMediaFromDisk($file->getClientOriginalPath(), FileUploadConfiguration::disk())
            ->usingName(File::name($file->getClientOriginalName()))
            ->usingFileName($file->getClientOriginalName())
            ->withCustomProperties($customProperties)
            ->toMediaCollection();
    }
}
