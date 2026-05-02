<?php

namespace Metafori\Archeo\Jobs\Concerns;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

trait StreamsDiskFiles
{
    private function streamFromDisk(string $disk, string $relativePath, string $dest): void
    {
        $readStream = Storage::disk($disk)->readStream($relativePath);

        if (! is_resource($readStream)) {
            throw new RuntimeException("Could not read PDF from disk '{$disk}' at '{$relativePath}'.");
        }

        $destHandle = fopen($dest, 'wb');

        if (! is_resource($destHandle)) {
            fclose($readStream);
            throw new RuntimeException("Could not open temp file for writing: '{$dest}'.");
        }

        try {
            stream_copy_to_stream($readStream, $destHandle);
        } finally {
            fclose($destHandle);
            fclose($readStream);
        }
    }

    private function streamToDisk(string $disk, string $relativePath, string $src): void
    {
        $srcHandle = fopen($src, 'rb');

        if (! is_resource($srcHandle)) {
            throw new RuntimeException("Could not open temp file for reading: '{$src}'.");
        }

        try {
            $ok = Storage::disk($disk)->put($relativePath, $srcHandle);
        } finally {
            fclose($srcHandle);
        }

        if (! $ok) {
            throw new RuntimeException("Failed to write file to disk '{$disk}' at '{$relativePath}'.");
        }
    }
}
