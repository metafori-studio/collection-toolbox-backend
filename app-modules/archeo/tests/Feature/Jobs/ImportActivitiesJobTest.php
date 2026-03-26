<?php

namespace Metafori\Archeo\Tests\Feature\Jobs;

use Illuminate\Support\Facades\Storage;
use Metafori\Archeo\Jobs\ImportActivitiesJob;
use Metafori\Archeo\Models\ActivityImport;
use Metafori\Core\Models\User;
use RuntimeException;

it('has the correct timeout value', function () {
    $user = User::factory()->create();
    $job = new ImportActivitiesJob('local', 'temp/test.xlsx', 'test.xlsx', $user);

    expect($job->timeout)->toBe(300);
});

it('has the correct number of tries', function () {
    $user = User::factory()->create();
    $job = new ImportActivitiesJob('local', 'temp/test.xlsx', 'test.xlsx', $user);

    expect($job->tries)->toBe(3);
});

it('has the correct backoff value', function () {
    $user = User::factory()->create();
    $job = new ImportActivitiesJob('local', 'temp/test.xlsx', 'test.xlsx', $user);

    expect($job->backoff)->toBe(60);
});

it('creates an activity import record with processing status when handled', function () {
    Storage::fake('local');
    Storage::disk('local')->put('temp-imports/test.xlsx', 'fake-excel-content');

    $user = User::factory()->create();
    $job = new ImportActivitiesJob('local', 'temp-imports/test.xlsx', 'test.xlsx', $user);
    $job->handle();

    $this->assertDatabaseHas('archeo_activity_imports', [
        'file_name' => 'test.xlsx',
        'path' => 'temp-imports/test.xlsx',
        'disk' => 'local',
        'user_id' => $user->id,
    ]);
});

it('sets activity import status to complete on successful handle', function () {
    Storage::fake('local');
    Storage::disk('local')->put('temp-imports/test.xlsx', 'fake-excel-content');

    $user = User::factory()->create();
    $job = new ImportActivitiesJob('local', 'temp-imports/test.xlsx', 'test.xlsx', $user);
    $job->handle();

    $import = ActivityImport::where('file_name', 'test.xlsx')->first();

    expect($import)->not->toBeNull()
        ->and($import->status)->toBe(ActivityImport::STATUS_COMPLETE);
});

it('deletes the file from storage after successful handle', function () {
    Storage::fake('local');
    Storage::disk('local')->put('temp-imports/test.xlsx', 'fake-excel-content');

    $user = User::factory()->create();
    $job = new ImportActivitiesJob('local', 'temp-imports/test.xlsx', 'test.xlsx', $user);
    $job->handle();

    Storage::disk('local')->assertMissing('temp-imports/test.xlsx');
});


it('deletes the file from storage in the failed method', function () {
    Storage::fake('local');
    Storage::disk('local')->put('temp-imports/test.xlsx', 'fake-excel-content');

    $user = User::factory()->create();
    $job = new ImportActivitiesJob('local', 'temp-imports/test.xlsx', 'test.xlsx', $user);
    $job->failed(new RuntimeException('Test failure'));

    Storage::disk('local')->assertMissing('temp-imports/test.xlsx');
});

it('stores the disk, relative path, original filename and user in the job', function () {
    $user = User::factory()->create();
    $job = new ImportActivitiesJob('s3', 'uploads/test.xlsx', 'original.xlsx', $user);

    expect($job->disk)->toBe('s3')
        ->and($job->relativePath)->toBe('uploads/test.xlsx')
        ->and($job->originalFileName)->toBe('original.xlsx')
        ->and($job->user->id)->toBe($user->id);
});

it('does not delete the file if it does not exist in the failed method', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $job = new ImportActivitiesJob('local', 'temp-imports/nonexistent.xlsx', 'nonexistent.xlsx', $user);

    // Should not throw an exception even if file is missing
    expect(fn () => $job->failed(new RuntimeException('Test failure')))->not->toThrow(\Exception::class);
});