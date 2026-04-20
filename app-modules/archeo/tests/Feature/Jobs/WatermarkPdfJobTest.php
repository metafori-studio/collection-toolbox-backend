<?php

use Filament\Notifications\DatabaseNotification;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Metafori\Archeo\Jobs\WatermarkPdfJob;
use Metafori\Archeo\Listeners\WatermarkPdfOnUploadListener;
use Metafori\Archeo\Models\Activity;
use Metafori\Core\Models\User;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

beforeEach(function () {
    Storage::fake('public');
    Queue::fake();
});

it('dispatches WatermarkPdfJob with the authenticated user when a PDF is added to the pdfs collection', function () {
    $user = User::factory()->create();
    Auth::login($user);

    $activity = Activity::factory()->create();
    $pdfPath = Storage::disk('public')->path('test.pdf');
    file_put_contents($pdfPath, '%PDF-1.4 fake content');

    $activity->addMedia($pdfPath)
        ->usingFileName('test.pdf')
        ->toMediaCollection('pdfs', 'public');

    Queue::assertPushed(WatermarkPdfJob::class, fn ($job) => $job->user?->is($user));
});

it('does not dispatch WatermarkPdfJob for non-pdf collections', function () {
    $media = new Media;
    $media->collection_name = 'gallery_images';
    $media->mime_type = 'image/jpeg';
    $media->id = 999;

    $listener = new WatermarkPdfOnUploadListener;
    $listener->handle(new MediaHasBeenAddedEvent($media));

    Queue::assertNotPushed(WatermarkPdfJob::class);
});

it('does not dispatch WatermarkPdfJob for non-pdf mime type in pdfs collection', function () {
    $media = new Media;
    $media->collection_name = 'pdfs';
    $media->mime_type = 'image/jpeg';
    $media->id = 999;

    $listener = new WatermarkPdfOnUploadListener;
    $listener->handle(new MediaHasBeenAddedEvent($media));

    Queue::assertNotPushed(WatermarkPdfJob::class);
});

it('does nothing when no watermark image is configured', function () {
    Config::set('archeo.watermark_image', null);
    Process::fake();

    $activity = Activity::factory()->create();
    $pdfPath = sys_get_temp_dir().'/test_wm_no_config.pdf';
    file_put_contents($pdfPath, '%PDF-1.4 fake content');

    $activity->addMedia($pdfPath)
        ->usingFileName('test.pdf')
        ->toMediaCollection('pdfs', 'public');

    $media = $activity->getMedia('pdfs')->first();

    (new WatermarkPdfJob($media->id))->handle();

    Process::assertNothingRan();
});

it('does nothing when the watermark image file does not exist on disk', function () {
    Config::set('archeo.watermark_image', '/nonexistent/watermark.png');
    Process::fake();

    $activity = Activity::factory()->create();
    $pdfPath = sys_get_temp_dir().'/test_wm_missing_file.pdf';
    file_put_contents($pdfPath, '%PDF-1.4 fake content');

    $activity->addMedia($pdfPath)
        ->usingFileName('test.pdf')
        ->toMediaCollection('pdfs', 'public');

    $media = $activity->getMedia('pdfs')->first();

    (new WatermarkPdfJob($media->id))->handle();

    Process::assertNothingRan();
});

it('applies watermark and replaces the file in storage', function () {
    $watermarkPng = tempnam(sys_get_temp_dir(), 'wm_').'.png';
    file_put_contents($watermarkPng, 'fake-png-data');
    Config::set('archeo.watermark_image', $watermarkPng);

    $activity = Activity::factory()->create();
    $originalContent = '%PDF-1.4 original content';
    $watermarkedContent = '%PDF-1.4 watermarked content';

    $pdfPath = sys_get_temp_dir().'/test_wm_apply.pdf';
    file_put_contents($pdfPath, $originalContent);

    Process::fake(function (PendingProcess $process) use ($watermarkedContent) {
        $command = $process->command;

        // identify call — return dimensions
        if (in_array('identify', $command)) {
            return Process::result(output: '595x842', exitCode: 0);
        }

        // magick canvas/composite call — write the stamp file
        // qpdf call — write the watermarked output
        $outputArg = collect($command)->last();
        if (str_ends_with((string) $outputArg, '.pdf') || str_starts_with((string) $outputArg, 'pdf:')) {
            $path = str_starts_with($outputArg, 'pdf:') ? substr($outputArg, 4) : $outputArg;
            file_put_contents($path, $watermarkedContent);
        }

        return Process::result(exitCode: 0);
    });

    $activity->addMedia($pdfPath)
        ->usingFileName('report.pdf')
        ->toMediaCollection('pdfs', 'public');

    $media = $activity->getMedia('pdfs')->first();

    (new WatermarkPdfJob($media->id))->handle();

    $media->refresh();
    expect($media->size)->toBe(strlen($watermarkedContent));

    $storedContent = Storage::disk('public')->get($media->getPathRelativeToRoot());
    expect($storedContent)->toBe($watermarkedContent);

    @unlink($watermarkPng);
});

it('sends a notification to the user after successful watermarking', function () {
    Notification::fake();

    $watermarkPng = tempnam(sys_get_temp_dir(), 'wm_').'.png';
    file_put_contents($watermarkPng, 'fake-png-data');
    Config::set('archeo.watermark_image', $watermarkPng);

    $user = User::factory()->create();
    $activity = Activity::factory()->create();

    $pdfPath = sys_get_temp_dir().'/test_wm_notify.pdf';
    file_put_contents($pdfPath, '%PDF-1.4 content');
    $watermarkedContent = '%PDF-1.4 watermarked';

    Process::fake(function (PendingProcess $process) use ($watermarkedContent) {
        $command = $process->command;

        if (in_array('identify', $command)) {
            return Process::result(output: '595x842', exitCode: 0);
        }

        $outputArg = collect($command)->last();
        if (str_ends_with((string) $outputArg, '.pdf') || str_starts_with((string) $outputArg, 'pdf:')) {
            $path = str_starts_with($outputArg, 'pdf:') ? substr($outputArg, 4) : $outputArg;
            file_put_contents($path, $watermarkedContent);
        }

        return Process::result(exitCode: 0);
    });

    $activity->addMedia($pdfPath)
        ->usingFileName('report.pdf')
        ->toMediaCollection('pdfs', 'public');

    $media = $activity->getMedia('pdfs')->first();

    (new WatermarkPdfJob($media->id, $user))->handle();

    Notification::assertSentTo($user, DatabaseNotification::class,
        fn ($notification) => $notification->data['title'] === __('archeo::activities.notifications.pdf_watermarked.title')
    );

    @unlink($watermarkPng);
});

it('does nothing when the media record no longer exists', function () {
    Process::fake();

    (new WatermarkPdfJob(999999))->handle();

    Process::assertNothingRan();
});
