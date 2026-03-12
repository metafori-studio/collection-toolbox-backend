<?php

use App\Jobs\ConvertXlsToCsvJob;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

it('converts an excel file to csv', function () {
    Storage::fake('local');

    // 1. Create a dummy Excel file
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Header1');
    $sheet->setCellValue('B1', 'Header2');
    $sheet->setCellValue('A2', 'Value1');
    $sheet->setCellValue('B2', 'Value2');

    $writer = new Xlsx($spreadsheet);
    $tempFile = tempnam(sys_get_temp_dir(), 'test_excel');
    $writer->save($tempFile);

    // Put it into the fake storage
    Storage::disk('local')->put('test.xlsx', file_get_contents($tempFile));
    unlink($tempFile);

    // 2. Dispatch the job
    $job = new ConvertXlsToCsvJob('test.xlsx', 'test.csv', 'local');
    $job->handle();

    // 3. Assert the CSV file exists
    Storage::disk('local')->assertExists('test.csv');

    // 4. Assert content
    $csvContent = Storage::disk('local')->get('test.csv');

    // Note: Csv writer might add quotes
    expect($csvContent)->toContain('Header1')
        ->and($csvContent)->toContain('Header2')
        ->and($csvContent)->toContain('Value1')
        ->and($csvContent)->toContain('Value2');
});

it('throws an exception if the input file does not exist', function () {
    Storage::fake('local');

    $job = new ConvertXlsToCsvJob('non-existent.xlsx', 'output.csv', 'local');

    expect(fn () => $job->handle())->toThrow(InvalidArgumentException::class);
});
