<?php

namespace Metafori\Archeo\Tests\Feature\Models;

use Metafori\Archeo\Models\Activity;
use Metafori\Archeo\Models\Gallery;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

it('has the correct default media disk constant', function () {
    expect(Gallery::DEFAULT_MEDIA_DISK)->toBe('public');
});

it('includes description in fillable attributes', function () {
    $gallery = new Gallery;

    expect($gallery->getFillable())->toContain('description');
});

it('includes all expected fillable attributes', function () {
    $gallery = new Gallery;
    $fillable = $gallery->getFillable();

    expect($fillable)
        ->toContain('activity_id')
        ->toContain('title')
        ->toContain('description')
        ->toContain('sort_order');
});

it('registers gallery_images media collection using the default media disk', function () {
    $activity = Activity::factory()->create();
    $gallery = Gallery::factory()->create(['activity_id' => $activity->id]);

    $collections = $gallery->getMediaCollections();
    $galleryCollection = collect($collections)->first(fn ($c) => $c->name === 'gallery_images');

    expect($galleryCollection)->not->toBeNull()
        ->and($galleryCollection->diskName)->toBe(Gallery::DEFAULT_MEDIA_DISK);
});

it('registers gallery_images collection accepting correct mime types', function () {
    $activity = Activity::factory()->create();
    $gallery = Gallery::factory()->create(['activity_id' => $activity->id]);

    $collections = $gallery->getMediaCollections();
    $galleryCollection = collect($collections)->first(fn ($c) => $c->name === 'gallery_images');

    expect($galleryCollection)->not->toBeNull()
        ->and($galleryCollection->acceptsMimeTypes)->toContain('image/jpeg')
        ->and($galleryCollection->acceptsMimeTypes)->toContain('image/png')
        ->and($galleryCollection->acceptsMimeTypes)->toContain('image/webp')
        ->and($galleryCollection->acceptsMimeTypes)->toContain('image/gif');
});

it('belongs to an activity', function () {
    $activity = Activity::factory()->create();
    $gallery = Gallery::factory()->create(['activity_id' => $activity->id]);

    expect($gallery->activity)->toBeInstanceOf(Activity::class)
        ->and($gallery->activity->id)->toBe($activity->id);
});

it('can be created with a title', function () {
    $activity = Activity::factory()->create();
    $gallery = Gallery::factory()->create([
        'activity_id' => $activity->id,
        'title' => 'Test Gallery',
    ]);

    expect($gallery->title)->toBe('Test Gallery');
});