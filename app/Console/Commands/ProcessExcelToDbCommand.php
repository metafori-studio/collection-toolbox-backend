<?php

namespace App\Console\Commands;

use App\Jobs\ProcessExcelToPostgresJob;
use Illuminate\Console\Command;

class ProcessExcelToDbCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-excel-to-db {input} {--disk=local} {--filename=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch a job to process an Excel file and save content to the database';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $input = $this->argument('input');
        $disk = $this->option('disk');
        $filename = $this->option('filename') ?: basename($input);

        $this->info("Dispatching job to process Excel to DB: {$input}");

        ProcessExcelToPostgresJob::dispatch($input, $filename, $disk);

        $this->info('Job dispatched successfully to the default queue.');
    }
}
