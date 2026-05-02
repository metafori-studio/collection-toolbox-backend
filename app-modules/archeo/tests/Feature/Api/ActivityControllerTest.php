<?php

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Metafori\Archeo\Jobs\WatermarkPdfJob;
use Metafori\Archeo\Models\Activity;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

function markAsWatermarked(Media $media): void
{
    $conversions = $media->generated_conversions ?? [];
    $conversions['watermarked'] = true;
    $media->generated_conversions = $conversions;
    $media->save();
}

beforeEach(function () {
    Storage::fake('public');
    Queue::fake();
});

it('dispatches WatermarkPdfJob for each PDF without a watermarked conversion on show', function () {
    $activity = Activity::factory()->create();

    $pdfPath = tempnam(sys_get_temp_dir(), 'test_controller_wm_');
    file_put_contents($pdfPath, '%PDF-1.4 content');

    $activity->addMedia($pdfPath)
        ->usingFileName('report.pdf')
        ->toMediaCollection('pdfs', 'public');
    @unlink($pdfPath);

    $this->getJson(url("api/archeo/activities/{$activity->activity_number}"))
        ->assertOk();

    Queue::assertPushed(WatermarkPdfJob::class, 1);
});

it('does not dispatch WatermarkPdfJob for PDFs that already have a watermarked conversion', function () {
    $activity = Activity::factory()->create();

    $pdfPath = tempnam(sys_get_temp_dir(), 'test_controller_wm_skip_');
    file_put_contents($pdfPath, '%PDF-1.4 content');

    $activity->addMedia($pdfPath)
        ->usingFileName('report.pdf')
        ->toMediaCollection('pdfs', 'public');
    @unlink($pdfPath);

    markAsWatermarked($activity->getMedia('pdfs')->first());

    Queue::fake();

    $this->getJson(url("api/archeo/activities/{$activity->activity_number}"))
        ->assertOk();

    Queue::assertNotPushed(WatermarkPdfJob::class);
});

it('returns watermarked_url as null when conversion has not been generated', function () {
    $activity = Activity::factory()->create();

    $pdfPath = tempnam(sys_get_temp_dir(), 'test_controller_url_');
    file_put_contents($pdfPath, '%PDF-1.4 content');

    $activity->addMedia($pdfPath)
        ->usingFileName('report.pdf')
        ->toMediaCollection('pdfs', 'public');
    @unlink($pdfPath);

    $response = $this->getJson(url("api/archeo/activities/{$activity->activity_number}"))
        ->assertOk();

    expect($response->json('data.pdfs.0.watermarked_url'))->toBeNull();
});

it('returns watermarked_url when conversion has been generated', function () {
    $activity = Activity::factory()->create();

    $pdfPath = tempnam(sys_get_temp_dir(), 'test_controller_url_set_');
    file_put_contents($pdfPath, '%PDF-1.4 content');

    $activity->addMedia($pdfPath)
        ->usingFileName('report.pdf')
        ->toMediaCollection('pdfs', 'public');
    @unlink($pdfPath);

    markAsWatermarked($activity->getMedia('pdfs')->first());

    $response = $this->getJson(url("api/archeo/activities/{$activity->activity_number}"))
        ->assertOk();

    expect($response->json('data.pdfs.0.watermarked_url'))->not->toBeNull()
        ->toContain('report-watermarked.pdf');
});
