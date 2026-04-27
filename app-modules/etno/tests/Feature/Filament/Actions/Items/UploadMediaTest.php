<?php

namespace Metafori\Etno\Tests\Feature\Filament\Actions\Items;

use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Queue;
use Metafori\Core\Models\User;
use Metafori\Etno\Filament\Resources\Items\ItemResource;
use Metafori\Etno\Filament\Resources\Items\Pages\EditItem;
use Metafori\Etno\Filament\Resources\Items\RelationManagers\MediaRelationManager;
use Metafori\Etno\Jobs\ProcessItemMediaUpload;
use Metafori\Etno\Models\Item;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::factory()->admin()->create());
});

it('uploads media files and assigns custom properties', function () {
    Queue::fake();

    $item = Item::factory()->create();

    $image = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');
    $transcript = UploadedFile::fake()->createWithContent('test.txt', 'text');

    livewire(EditItem::class, [
        'parentRecord' => $item->document,
        'record' => $item->id,
    ])
        ->callAction(TestAction::make('upload_media'), [
            'files' => [
                $image,
                $transcript,
            ],
        ])
        ->assertHasNoErrors()
        ->assertRedirect(ItemResource::getUrl('edit', [
            'document' => $item->document,
            'record' => $item,
            'relation' => 'media',
        ]));

    Queue::assertPushed(
        ProcessItemMediaUpload::class,
        fn ($job) => $job->item->id === $item->id
            && Arr::get($job->customProperties, 'transcripts.txt') === 'text'
    );

    livewire(MediaRelationManager::class, [
        'ownerRecord' => $item,
        'pageClass' => EditItem::class,
    ])->assertSee('1 media file is currently being processed in the background.');
});

it('cannot assign media files of various mime types to one item', function () {
    $item = Item::factory()->create();

    $image = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');
    $video = UploadedFile::fake()->create('test.mp4', 100, 'video/mp4');

    livewire(EditItem::class, [
        'parentRecord' => $item->document,
        'record' => $item->id,
    ])
        ->callAction(TestAction::make('upload_media'), [
            'files' => [
                $image,
                $video,
            ],
        ])
        ->assertHasErrors([
            'mountedActions.0.data.media' => 'The media type of the file must match the other media files.',
        ])
        ->assertNoRedirect();
});

it('cannot assign media files of different mime type than already set', function () {
    $item = Item::factory()->create();
    $item->addMedia(UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg'))->toMediaCollection();

    $video = UploadedFile::fake()->create('test.mp4', 100, 'video/mp4');

    livewire(EditItem::class, [
        'parentRecord' => $item->document,
        'record' => $item->id,
    ])
        ->callAction(TestAction::make('upload_media'), [
            'files' => [
                $video,
            ],
        ])
        ->assertHasErrors([
            'mountedActions.0.data.media' => 'The media type of the file must match the other item\'s media files.',
        ])
        ->assertNoRedirect();
});

it('can reorder media files by file name', function () {
    $item = Item::factory()->create();

    $image1 = UploadedFile::fake()->create('test1.jpg', 100, 'image/jpeg');
    $image2 = UploadedFile::fake()->create('test2.jpg', 100, 'image/jpeg');

    livewire(EditItem::class, [
        'parentRecord' => $item->document,
        'record' => $item->id,
    ])
        ->mountAction('upload_media')
        ->fillForm([
            'files' => [
                $image2,
                $image1,
            ],
        ])
        ->callAction(TestAction::make('order_by_name')->schemaComponent('media'))
        ->callMountedAction();

    expect($item->media->firstWhere('file_name', 'test1.jpg')->order_column)->toBe(1)
        ->and($item->media->firstWhere('file_name', 'test2.jpg')->order_column)->toBe(2);
});

it('cannot upload transcripts without media files', function () {
    $item = Item::factory()->create();

    $transcript = UploadedFile::fake()->createWithContent('test.txt', 'text');

    livewire(EditItem::class, [
        'parentRecord' => $item->document,
        'record' => $item->id,
    ])
        ->callAction(TestAction::make('upload_media'), [
            'files' => [
                $transcript,
            ],
        ])
        ->assertHasErrors([
            'mountedActions.0.data.files' => 'Cannot upload transcripts without corresponding media files.',
        ])
        ->assertNoRedirect();
});
