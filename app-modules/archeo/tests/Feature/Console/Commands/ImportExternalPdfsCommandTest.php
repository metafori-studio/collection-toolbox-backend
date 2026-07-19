<?php

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Metafori\Archeo\Models\Activity;

beforeEach(function () {
    Storage::fake();
    Storage::fake('public');
    Queue::fake();
});

it('can import and assign a PDF named with exact CVS number to matching activity', function () {
    $activity = Activity::factory()->create(['cvs_number' => 123]);

    Storage::put('import_test/123.pdf', '%PDF-1.4 fake content');
    $pdfPath = Storage::path('import_test/123.pdf');

    $this->artisan('archeo:import-pdfs', [
        'path' => $pdfPath,
    ])
        ->expectsOutputToContain('Assigning 123.pdf to 1 activity/activities matching CVS: 123')
        ->assertExitCode(0);

    expect($activity->fresh()->hasMedia('pdfs'))->toBeTrue();
    expect($activity->fresh()->getFirstMedia('pdfs')->file_name)->toBe('123.pdf');
    expect(Storage::exists('import_test/123.pdf'))->toBeTrue(); // defaults to preserving original file
});

it('can extract CVS number from various file names', function () {
    $activity1 = Activity::factory()->create(['cvs_number' => 456]);
    $activity2 = Activity::factory()->create(['cvs_number' => 789]);
    $activity3 = Activity::factory()->create(['cvs_number' => 999]);
    $activity4 = Activity::factory()->create(['cvs_number' => 20853]);

    Storage::put('import_test_various/456_file.pdf', '%PDF-1.4 fake content');
    Storage::put('import_test_various/789-file.pdf', '%PDF-1.4 fake content');
    Storage::put('import_test_various/999.pdf', '%PDF-1.4 fake content');
    Storage::put('import_test_various/20853_23ns.pdf', '%PDF-1.4 fake content');

    $tempDir = Storage::path('import_test_various');

    $this->artisan('archeo:import-pdfs', [
        'path' => $tempDir,
    ])
        ->expectsOutputToContain('Assigning 456_file.pdf to 1 activity/activities matching CVS: 456')
        ->expectsOutputToContain('Assigning 789-file.pdf to 1 activity/activities matching CVS: 789')
        ->expectsOutputToContain('Assigning 999.pdf to 1 activity/activities matching CVS: 999')
        ->expectsOutputToContain('Assigning 20853_23ns.pdf to 1 activity/activities matching CVS: 20853')
        ->assertExitCode(0);

    expect($activity1->fresh()->hasMedia('pdfs'))->toBeTrue();
    expect($activity2->fresh()->hasMedia('pdfs'))->toBeTrue();
    expect($activity3->fresh()->hasMedia('pdfs'))->toBeTrue();
    expect($activity4->fresh()->hasMedia('pdfs'))->toBeTrue();
});

it('can assign a PDF to multiple matching activities sharing the same CVS number', function () {
    $activity1 = Activity::factory()->create(['cvs_number' => 555]);
    $activity2 = Activity::factory()->create(['cvs_number' => 555]);

    Storage::put('import_test_multiple/555.pdf', '%PDF-1.4 fake content');
    $pdfPath = Storage::path('import_test_multiple/555.pdf');

    $this->artisan('archeo:import-pdfs', [
        'path' => $pdfPath,
    ])
        ->expectsOutputToContain('Assigning 555.pdf to 2 activity/activities matching CVS: 555')
        ->assertExitCode(0);

    expect($activity1->fresh()->hasMedia('pdfs'))->toBeTrue();
    expect($activity2->fresh()->hasMedia('pdfs'))->toBeTrue();
});

it('deletes the source file after successful import when --delete-sources is provided', function () {
    $activity = Activity::factory()->create(['cvs_number' => 888]);

    Storage::put('import_test_delete/888.pdf', '%PDF-1.4 fake content');
    $pdfPath = Storage::path('import_test_delete/888.pdf');

    expect(Storage::exists('import_test_delete/888.pdf'))->toBeTrue();

    $this->artisan('archeo:import-pdfs', [
        'path' => $pdfPath,
        '--delete-sources' => true,
    ])
        ->assertExitCode(0);

    expect($activity->fresh()->hasMedia('pdfs'))->toBeTrue();
    expect(Storage::exists('import_test_delete/888.pdf'))->toBeFalse(); // deleted
});

it('skips and warns when no CVS number can be extracted', function () {
    Storage::put('import_test_no_cvs/no_digits_here.pdf', '%PDF-1.4 fake content');
    $pdfPath = Storage::path('import_test_no_cvs/no_digits_here.pdf');

    $this->artisan('archeo:import-pdfs', [
        'path' => $pdfPath,
    ])
        ->expectsOutputToContain('Could not extract CVS number from filename: no_digits_here')
        ->assertExitCode(0);
});

it('skips and warns when no activity matches the CVS number', function () {
    Storage::put('import_test_no_match/99999.pdf', '%PDF-1.4 fake content');
    $pdfPath = Storage::path('import_test_no_match/99999.pdf');

    $this->artisan('archeo:import-pdfs', [
        'path' => $pdfPath,
    ])
        ->expectsOutputToContain('No activities found with CVS number: 99999 (File: 99999)')
        ->assertExitCode(0);
});
