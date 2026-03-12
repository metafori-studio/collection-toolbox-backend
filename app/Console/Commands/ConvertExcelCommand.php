<?php

namespace App\Console\Commands;

use App\Jobs\ConvertXlsToCsvJob;
use Illuminate\Console\Command;

class ConvertExcelCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:convert-excel {input} {output} {--disk=local}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch a job to convert an Excel file to CSV';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $input = $this->argument('input');
        $output = $this->argument('output');
        $disk = $this->option('disk');

        $this->info("Dispatching job for: {$input}");

        ConvertXlsToCsvJob::dispatch($input, $output, $disk);

        $this->info('Job dispatched successfully to the default queue.');
    }
}
