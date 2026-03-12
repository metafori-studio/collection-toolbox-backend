<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Metafori\Archeo\Services\ActivityExcelParser;

class ProcessExcelToPostgresJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     *
     * @param  string  $inputPath  The relative path to the Excel file in storage.
     * @param  string  $originalFileName  The original name of the file (used for metadata).
     * @param  string|null  $disk  The storage disk to use. Defaults to 'local'.
     */
    public function __construct(
        public string $inputPath,
        public string $originalFileName,
        public ?string $disk = 'local'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ActivityExcelParser $parser): void
    {
        $storage = Storage::disk($this->disk ?? 'local');

        if (! $storage->exists($this->inputPath)) {
            throw new \InvalidArgumentException("Input file does not exist: {$this->inputPath}");
        }

        $localAbsolutePath = $storage->path($this->inputPath);

        // The ActivityExcelParser handles the reading and DB insertion/update
        $parser->importFromPath($localAbsolutePath, $this->originalFileName);
    }
}
