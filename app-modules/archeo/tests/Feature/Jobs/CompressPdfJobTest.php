<?php

use Filament\Notifications\DatabaseNotification;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Metafori\Archeo\Jobs\CompressPdfJob;
use Metafori\Archeo\Listeners\CompressPdfOnUploadListener;
use Metafori\Archeo\Models\Activity;
use Metafori\Core\Models\User;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

beforeEach(function () {
    Storage::fake('public');
});

it('dispatches CompressPdfJob with the authenticated user when a PDF is added to the pdfs collection', function () {
    Queue::fake();

    $user = User::factory()->create();
    Auth::login($user);

    $activity = Activity::factory()->create();
    $pdfPath = Storage::disk('public')->path('test.pdf');
    file_put_contents($pdfPath, '%PDF-1.4 fake content');

    $activity->addMedia($pdfPath)
        ->usingFileName('test.pdf')
        ->toMediaCollection('pdfs', 'public');

    Queue::assertPushed(CompressPdfJob::class, fn ($job) => $job->user?->is($user));
});

it('does not dispatch CompressPdfJob for non-pdf collections', function () {
    Queue::fake();

    $media = new Media;
    $media->collection_name = 'gallery_images';
    $media->mime_type = 'image/jpeg';
    $media->id = 999;

    $listener = new CompressPdfOnUploadListener;
    $listener->handle(new MediaHasBeenAddedEvent($media));

    Queue::assertNotPushed(CompressPdfJob::class);
});

it('does not dispatch CompressPdfJob for non-pdf mime type in pdfs collection', function () {
    Queue::fake();

    $media = new Media;
    $media->collection_name = 'pdfs';
    $media->mime_type = 'image/jpeg';
    $media->id = 999;

    $listener = new CompressPdfOnUploadListener;
    $listener->handle(new MediaHasBeenAddedEvent($media));

    Queue::assertNotPushed(CompressPdfJob::class);
});

it('replaces the file with the compressed version when ghostscript reduces file size', function () {
    $activity = Activity::factory()->create();

    $originalContent = str_repeat('%PDF-1.4 uncompressed content', 100);
    $compressedContent = '%PDF-1.4 compressed';

    $pdfPath = sys_get_temp_dir().'/test_original.pdf';
    file_put_contents($pdfPath, $originalContent);

    Process::fake(function (PendingProcess $process) use ($compressedContent) {
        $outputFile = collect($process->command)->first(fn ($arg) => str_starts_with($arg, '-sOutputFile='));
        $outputPath = substr($outputFile, strlen('-sOutputFile='));
        file_put_contents($outputPath, $compressedContent);

        return Process::result(exitCode: 0);
    });

    $activity->addMedia($pdfPath)
        ->usingFileName('test.pdf')
        ->toMediaCollection('pdfs', 'public');

    $media = $activity->getMedia('pdfs')->first();

    (new CompressPdfJob($media->id))->handle();

    $media->refresh();
    expect($media->size)->toBe(strlen($compressedContent));

    $storedContent = Storage::disk('public')->get($media->getPathRelativeToRoot());
    expect($storedContent)->toBe($compressedContent);
});

it('keeps the original file when ghostscript does not reduce file size', function () {
    $activity = Activity::factory()->create();

    $originalContent = '%PDF-1.4 already compressed';

    $pdfPath = sys_get_temp_dir().'/test_small.pdf';
    file_put_contents($pdfPath, $originalContent);

    Process::fake(function (PendingProcess $process) use ($originalContent) {
        $outputFile = collect($process->command)->first(fn ($arg) => str_starts_with($arg, '-sOutputFile='));
        $outputPath = substr($outputFile, strlen('-sOutputFile='));
        file_put_contents($outputPath, $originalContent.str_repeat('X', 100));

        return Process::result(exitCode: 0);
    });

    $activity->addMedia($pdfPath)
        ->usingFileName('small.pdf')
        ->toMediaCollection('pdfs', 'public');

    $media = $activity->getMedia('pdfs')->first();
    $originalSize = $media->size;

    (new CompressPdfJob($media->id))->handle();

    $media->refresh();
    expect($media->size)->toBe($originalSize);
});

it('sends a notification to the user after successful compression', function () {
    Notification::fake();

    $user = User::factory()->create();
    $activity = Activity::factory()->create();

    $originalContent = str_repeat('%PDF-1.4 uncompressed content', 100);
    $compressedContent = '%PDF-1.4 compressed';

    $pdfPath = sys_get_temp_dir().'/test_notify.pdf';
    file_put_contents($pdfPath, $originalContent);

    Process::fake(function (PendingProcess $process) use ($compressedContent) {
        $outputFile = collect($process->command)->first(fn ($arg) => str_starts_with($arg, '-sOutputFile='));
        $outputPath = substr($outputFile, strlen('-sOutputFile='));
        file_put_contents($outputPath, $compressedContent);

        return Process::result(exitCode: 0);
    });

    $activity->addMedia($pdfPath)
        ->usingFileName('report.pdf')
        ->toMediaCollection('pdfs', 'public');

    $media = $activity->getMedia('pdfs')->first();

    (new CompressPdfJob($media->id, $user))->handle();

    Notification::assertSentTo($user, DatabaseNotification::class,
        fn ($notification) => $notification->data['title'] === __('archeo::activities.notifications.pdf_compressed.title')
    );
});

it('does not send a notification when compression does not reduce file size', function () {
    Notification::fake();

    $user = User::factory()->create();
    $activity = Activity::factory()->create();

    $originalContent = '%PDF-1.4 already small';
    $pdfPath = sys_get_temp_dir().'/test_no_notify.pdf';
    file_put_contents($pdfPath, $originalContent);

    Process::fake(function (PendingProcess $process) use ($originalContent) {
        $outputFile = collect($process->command)->first(fn ($arg) => str_starts_with($arg, '-sOutputFile='));
        $outputPath = substr($outputFile, strlen('-sOutputFile='));
        file_put_contents($outputPath, $originalContent.str_repeat('X', 100));

        return Process::result(exitCode: 0);
    });

    $activity->addMedia($pdfPath)
        ->usingFileName('small.pdf')
        ->toMediaCollection('pdfs', 'public');

    $media = $activity->getMedia('pdfs')->first();

    (new CompressPdfJob($media->id, $user))->handle();

    Notification::assertNothingSent();
});

it('does nothing when the media record no longer exists', function () {
    Process::fake();

    (new CompressPdfJob(999999))->handle();

    Process::assertRanTimes(function () {}, 0);
});
