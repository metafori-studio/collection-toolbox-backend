<?php

use Illuminate\Http\UploadedFile;
use Metafori\Etno\Enums\MediaType;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Models\Media;

it('returns correct media type enum', function () {
    $item = Item::factory()
        ->withMedia(filename: 'document.pdf', collection: MediaType::Document->value, content: '%PDF-1.4')
        ->create();

    $media = $item->getFirstMedia(MediaType::Document->value);

    expect($media)->toBeInstanceOf(Media::class)
        ->and($media->getType())->toBe(MediaType::Document);
});

it('returns null for unknown collection name', function () {
    $item = Item::factory()->create();

    // The 'default' collection does not have mime-type validation in the Item model
    $media = $item->addMedia(UploadedFile::fake()->create('file.txt'))
        ->toMediaCollection();

    expect($media->getType())->toBeNull();
});
