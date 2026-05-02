<?php

namespace Metafori\Archeo\Jobs;

use Filament\Notifications\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use Metafori\Archeo\Jobs\Concerns\StreamsDiskFiles;
use Metafori\Core\Models\User;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class CompressPdfJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, StreamsDiskFiles;

    public int $timeout = 900; // 15 minutes

    public int $tries = 3;

    public int $backoff = 60;

    public function uniqueId(): string
    {
        return (string) $this->mediaId;
    }

    public function __construct(
        public readonly int $mediaId,
        public readonly ?User $user = null,
    ) {}

    public function handle(): void
    {
        $media = Media::find($this->mediaId);

        if (! $media) {
            return;
        }

        $relativePath = $media->getPathRelativeToRoot();
        $tempInput = tempnam(sys_get_temp_dir(), 'pdf_in_');
        $tempOutput = tempnam(sys_get_temp_dir(), 'pdf_out_');

        if ($tempInput === false || $tempOutput === false) {
            throw new RuntimeException('Could not create temporary files for PDF compression. Check available disk space on '.sys_get_temp_dir().'.');
        }

        try {
            $this->streamFromDisk($media->disk, $relativePath, $tempInput);

            $result = Process::timeout($this->timeout - 30)->run([
                config('archeo.ghostscript_binary', 'gs'),
                '-sDEVICE=pdfwrite',
                '-dCompatibilityLevel=1.4',
                '-dNOPAUSE',
                '-dQUIET',
                '-dBATCH',
                // Downsample colour and grayscale images to 150 dpi
                // using bicubic interpolation.
                '-dDownsampleColorImages=true',
                '-dDownsampleGrayImages=true',
                '-dFastWebView=true',
                '-dColorImageDownsampleType=/Bicubic',
                '-dGrayImageDownsampleType=/Bicubic',
                '-dColorImageResolution=150',
                '-dGrayImageResolution=150',
                "-sOutputFile={$tempOutput}",
                $tempInput,
            ]);

            if (! $result->successful()) {
                throw new RuntimeException('Ghostscript compression failed: '.$result->errorOutput());
            }

            $originalSize = $media->size;
            $compressedSize = filesize($tempOutput);

            // Treat a missing/empty output as a failure rather than overwriting
            // the original with a corrupt file.
            if ($compressedSize === false || $compressedSize === 0 || $compressedSize >= $originalSize) {
                return;
            }

            $this->streamToDisk($media->disk, $relativePath, $tempOutput);

            $media->update(['size' => $compressedSize]);

            if ($this->user) {
                Notification::make()
                    ->title(__('archeo::activities.notifications.pdf_compressed.title'))
                    ->body(__('archeo::activities.notifications.pdf_compressed.body', [
                        'name' => $media->name,
                    ]))
                    ->success()
                    ->sendToDatabase($this->user);
            }
        } finally {
            @unlink($tempInput);
            @unlink($tempOutput);
        }
    }

    public function failed(Throwable $exception): void
    {
        // Compression failure is non-critical — the original file remains intact.
    }
}
