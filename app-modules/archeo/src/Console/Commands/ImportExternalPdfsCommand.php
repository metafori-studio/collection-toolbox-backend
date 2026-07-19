<?php

namespace Metafori\Archeo\Console\Commands;

use Illuminate\Console\Command;
use Metafori\Archeo\Models\Activity;

class ImportExternalPdfsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'archeo:import-pdfs
                            {path : The path to the directory or single PDF file}
                            {--delete-sources : Delete the source PDFs inside the container after successful assignment}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import and assign external PDFs to activities by CVS number';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = $this->argument('path');
        $deleteSources = $this->option('delete-sources');

        if (! file_exists($path) && file_exists(storage_path($path))) {
            $path = storage_path($path);
        }

        if (! file_exists($path)) {
            $this->error("The path [{$path}] does not exist.");

            return 1;
        }

        $files = [];
        if (is_dir($path)) {
            $files = glob(rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'*.{pdf,PDF}', GLOB_BRACE);
        } elseif (is_file($path)) {
            if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf') {
                $files = [$path];
            } else {
                $this->error("The file [{$path}] is not a PDF.");

                return 1;
            }
        }

        if (empty($files) || $files === false) {
            $this->warn('No PDF files found to process.');

            return 0;
        }

        $this->info('Found '.count($files).' PDF file(s) to process.');

        $successCount = 0;
        $failedCount = 0;

        foreach ($files as $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $cvsNumber = $this->extractCvsNumber($filename);

            if ($cvsNumber === null) {
                $this->warn("Could not extract CVS number from filename: {$filename}");
                $failedCount++;

                continue;
            }

            $activities = Activity::where('cvs_number', $cvsNumber)->get();

            if ($activities->isEmpty()) {
                $this->warn("No activities found with CVS number: {$cvsNumber} (File: {$filename})");
                $failedCount++;

                continue;
            }

            $this->info("Assigning {$filename}.pdf to ".$activities->count()." activity/activities matching CVS: {$cvsNumber}");

            $assigned = false;
            foreach ($activities as $activity) {
                try {
                    $activity->addMedia($file)
                        ->usingName($filename)
                        ->usingFileName(basename($file))
                        ->preservingOriginal()
                        ->toMediaCollection('pdfs', config('archeo.pdfs_disk', 'public'));
                    $assigned = true;
                } catch (\Exception $e) {
                    $this->error("Failed to assign {$filename}.pdf to Activity {$activity->activity_number}: {$e->getMessage()}");
                }
            }

            if ($assigned) {
                $successCount++;
                if ($deleteSources) {
                    @unlink($file);
                }
            } else {
                $failedCount++;
            }
        }

        $this->info("Import completed. Successfully processed {$successCount} files. Failed/skipped {$failedCount} files.");

        return 0;
    }

    /**
     * Extract the CVS number from a filename.
     */
    private function extractCvsNumber(string $filename): ?int
    {
        if (preg_match('/^(\d+)/', $filename, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
