<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class ConvertXlsToCsvJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     *
     * @param  string  $inputPath  The relative path to the XLS file in the storage.
     * @param  string  $outputPath  The relative path where the CSV file should be saved.
     * @param  string|null  $disk  The storage disk to use. Defaults to 'local'.
     */
    public function __construct(
        public string $inputPath,
        public string $outputPath,
        public ?string $disk = 'local'
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $storage = Storage::disk($this->disk ?? 'local');

        if (! $storage->exists($this->inputPath)) {
            throw new \InvalidArgumentException("Input file does not exist: {$this->inputPath}");
        }

        $inputAbsolutePath = $storage->path($this->inputPath);
        $outputAbsolutePath = $storage->path($this->outputPath);

        // Ensure the directory for the output file exists.
        $outputDirectory = dirname($outputAbsolutePath);
        if (! is_dir($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }

        // Load the spreadsheet (XLS, XLSX, ODS, etc.)
        $spreadsheet = IOFactory::load($inputAbsolutePath);

        // Save as CSV
        $writer = new Csv($spreadsheet);
        $writer->save($outputAbsolutePath);
    }
}
