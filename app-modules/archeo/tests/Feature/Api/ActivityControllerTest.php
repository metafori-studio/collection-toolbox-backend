<?php

use Illuminate\Support\Facades\Config;
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
    Config::set('archeo.watermark_image', __DIR__.'/../../Fixtures/dummy-watermark.png');
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

it('returns original url when watermarked conversion has not been generated', function () {
    $activity = Activity::factory()->create();

    $pdfPath = tempnam(sys_get_temp_dir(), 'test_controller_url_');
    file_put_contents($pdfPath, '%PDF-1.4 content');

    $activity->addMedia($pdfPath)
        ->usingFileName('report.pdf')
        ->toMediaCollection('pdfs', 'public');
    @unlink($pdfPath);

    $response = $this->getJson(url("api/archeo/activities/{$activity->activity_number}"))
        ->assertOk();

    expect($response->json('data.pdfs.0.url'))->not->toBeNull()
        ->toContain('report.pdf');
});

it('returns watermarked url in url prop when conversion has been generated', function () {
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

    expect($response->json('data.pdfs.0.url'))->not->toBeNull()
        ->toContain('report-watermarked.pdf');
});

it('returns aggregations including jsonb array fields', function () {
    Activity::factory()->create([
        'activity_type' => 'Výskum',
        'author_ns' => ['Jan Novák', 'Peter Kováč'],
        'dating_ns' => ['Pravek', 'Stredovek'],
        'dating_ceans' => ['Halštat'],
        'dating_site_type' => ['Sídlisko'],
    ]);

    Activity::factory()->create([
        'activity_type' => 'Výskum',
        'author_ns' => ['Jan Novák'],
        'dating_ns' => ['Stredovek'],
        'dating_ceans' => ['Latén'],
        'dating_site_type' => ['Pohrebisko'],
    ]);

    $response = $this->getJson(url('api/archeo/activities/aggregations'))
        ->assertSuccessful();

    $authorNs = collect($response->json('data.author_ns'));
    expect($authorNs->firstWhere('value', 'Jan Novák'))->toMatchArray([
        'value' => 'Jan Novák',
        'label' => 'Jan Novák',
        'count' => 2,
    ])->and($authorNs->firstWhere('value', 'Peter Kováč'))->toMatchArray([
        'value' => 'Peter Kováč',
        'label' => 'Peter Kováč',
        'count' => 1,
    ]);

    $datingNs = collect($response->json('data.dating_ns'));
    expect($datingNs->firstWhere('value', 'Stredovek')['count'])->toBe(2)
        ->and($datingNs->firstWhere('value', 'Pravek')['count'])->toBe(1);

    $filtered = $this->getJson(url('api/archeo/activities?filter[author_ns][]=Peter+Kov%C3%A1%C4%8D'))
        ->assertSuccessful();

    expect($filtered->json('data'))->toHaveCount(1);
});
