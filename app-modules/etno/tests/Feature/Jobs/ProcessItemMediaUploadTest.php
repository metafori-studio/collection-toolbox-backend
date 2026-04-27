<?php

namespace Metafori\Etno\Tests\Feature\Jobs;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Metafori\Etno\Jobs\ProcessItemMediaUpload;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Repositories\ItemRepository;

it('processes item media upload and decrements cache count', function () {
    Queue::fake();
    Storage::fake(FileUploadConfiguration::disk());

    $item = Item::factory()->create();
    $repository = app(ItemRepository::class);

    $repository->incrementProcessingMediaCount($item);

    $fileName = 'test-file.jpg';
    Storage::disk(FileUploadConfiguration::disk())->put($fileName, 'fake image content');

    $job = new ProcessItemMediaUpload(
        item: $item,
        filePath: $fileName,
        originalName: 'test.jpg',
        mimeType: 'image/jpeg',
        customProperties: ['transcripts' => ['txt' => 'text']]
    );

    $job->handle($repository);

    $media = $item->fresh()->media->first();

    expect($media)->not->toBeNull()
        ->and($media->name)->toBe('test')
        ->and($media->file_name)->toBe('test.jpg')
        ->and($media->getCustomProperty('transcripts.txt'))->toBe('text');

    expect($repository->getProcessingMediaCount($item))->toBe(0);
});
