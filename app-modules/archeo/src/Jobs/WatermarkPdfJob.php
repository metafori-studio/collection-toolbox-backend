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
use Metafori\Archeo\Support\WatermarkedPdfPath;
use Metafori\Core\Models\User;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class WatermarkPdfJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, StreamsDiskFiles;

    public int $timeout = 600; // 10 minutes

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

        $watermarkImage = config('archeo.watermark_image');

        if (! $watermarkImage || ! file_exists($watermarkImage)) {
            return;
        }

        $relativePath = $media->getPathRelativeToRoot();
        $tempInput = tempnam(sys_get_temp_dir(), 'pdf_wm_in_');
        $tempStamp = tempnam(sys_get_temp_dir(), 'pdf_wm_stamp_');
        $tempOutput = tempnam(sys_get_temp_dir(), 'pdf_wm_out_');

        if ($tempInput === false || $tempStamp === false || $tempOutput === false) {
            throw new RuntimeException('Could not create temporary files for PDF watermarking. Check available disk space on '.sys_get_temp_dir().'.');
        }

        try {
            $this->streamFromDisk($media->disk, $relativePath, $tempInput);

            [$width, $height] = $this->getPageDimensions($tempInput);

            $watermarkWidth = (int) ($width * 0.7);

            $magickResult = Process::timeout(60)->run([
                config('archeo.magick_binary', 'magick'),
                '-size', "{$width}x{$height}",
                'canvas:none',
                '(',
                $watermarkImage,
                '-resize', "{$watermarkWidth}x",
                '-alpha', 'set',
                '-channel', 'A',
                '-evaluate', 'multiply', '0.3',
                '+channel',
                ')',
                '-gravity', 'center',
                '-composite',
                "pdf:{$tempStamp}",
            ]);

            if (! $magickResult->successful()) {
                throw new RuntimeException('ImageMagick failed to create watermark stamp: '.$magickResult->errorOutput());
            }

            // identify (≤30s) + magick (≤60s) + overhead — leave the rest for qpdf
            $qpdfResult = Process::timeout($this->timeout - 120)->run([
                config('archeo.qpdf_binary', 'qpdf'),
                $tempInput,
                '--overlay', $tempStamp,
                '--repeat=1-z',
                '--',
                $tempOutput,
            ]);

            if (! $qpdfResult->successful()) {
                throw new RuntimeException('qpdf failed to apply watermark (exit '.$qpdfResult->exitCode().'): '.$qpdfResult->errorOutput());
            }

            $this->streamToDisk($media->disk, WatermarkedPdfPath::forMedia($media), $tempOutput);

            $generatedConversions = $media->generated_conversions ?? [];
            $generatedConversions['watermarked'] = true;
            $media->generated_conversions = $generatedConversions;
            $media->save();

            if ($this->user) {
                Notification::make()
                    ->title(__('archeo::activities.notifications.pdf_watermarked.title'))
                    ->body(__('archeo::activities.notifications.pdf_watermarked.body', [
                        'name' => $media->name,
                    ]))
                    ->success()
                    ->sendToDatabase($this->user);
            }
        } finally {
            @unlink($tempInput);
            @unlink($tempStamp);
            @unlink($tempOutput);
        }
    }

    public function failed(Throwable $exception): void
    {
        // Watermarking failure is non-critical — the original file remains intact.
    }

    /**
     * Returns [width, height] in pixels for the first page of the PDF.
     *
     * @return array{int, int}
     */
    private function getPageDimensions(string $pdfPath): array
    {
        $result = Process::timeout(30)->run([
            config('archeo.magick_binary', 'magick'),
            'identify',
            '-format', '%wx%h',
            "{$pdfPath}[0]",
        ]);

        if (! $result->successful()) {
            throw new RuntimeException('Could not determine PDF dimensions: '.$result->errorOutput());
        }

        // identify may return multiple tokens if GS emits extra output; take the last one
        $token = trim(collect(preg_split('/\s+/', trim($result->output())))->last() ?? '');

        if (! preg_match('/^(\d+)x(\d+)$/', $token, $matches)) {
            throw new RuntimeException("Could not parse PDF dimensions from identify output: '{$result->output()}'.");
        }

        $width = (int) $matches[1];
        $height = (int) $matches[2];

        if ($width === 0 || $height === 0) {
            throw new RuntimeException('PDF reported zero dimensions.');
        }

        return [$width, $height];
    }
}
