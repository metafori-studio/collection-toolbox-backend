<?php

namespace Metafori\Etno\Tests\Feature\Filament\Actions\Items;

use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Metafori\Core\Models\User;
use Metafori\Etno\Filament\Resources\Items\ItemResource;
use Metafori\Etno\Filament\Resources\Items\Pages\EditItem;
use Metafori\Etno\Models\Item;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::factory()->admin()->create());
});

it('uploads media files and assigns custom properties', function () {
    $item = Item::factory()->create();

    $image = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');
    $transcript = UploadedFile::fake()->createWithContent('test.txt', 'text');

    livewire(EditItem::class, [
        'parentRecord' => $item->document,
        'record' => $item->id,
    ])
        ->mountAction('upload_media')
        ->set('mountedActions.0.files', [
            $image,
            $transcript,
        ])
        ->callMountedAction()
        ->assertHasNoActionErrors()
        ->assertRedirect(ItemResource::getUrl('edit', [
            'document' => $item->document,
            'record' => $item,
            'relation' => 'media',
        ]));

    $media = $item->media->first();
    expect($media)->not->toBeNull()
        ->and($media->name)->toBe('test')
        ->and($media->file_name)->toBe('test.jpg')
        ->and($media->getCustomProperty('transcripts.txt'))->toBe('text');
});

it('cannot assign media files of various mime types to one item', function () {
    $item = Item::factory()->create();

    $image = UploadedFile::fake()->create('test.jpg', 100, 'image/jpeg');
    $video = UploadedFile::fake()->create('test.mp4', 100, 'video/mp4');

    livewire(EditItem::class, [
        'parentRecord' => $item->document,
        'record' => $item->id,
    ])
        ->mountAction('upload_media')
        ->set('mountedActions.0.files', [
            $image,
            $video,
        ])
        ->callMountedAction()
        ->assertHasActionErrors([
            'files' => 'The media type of the file must match the other media files.',
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
        ->mountAction('upload_media')
        ->set('mountedActions.0.files', [
            $video,
        ])
        ->callMountedAction()
        ->assertHasActionErrors([
            'files' => 'The media type of the file must match the other item\'s media files.',
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
        ->set('mountedActions.0.files', [
            $image2,
            $image1,
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
        ->mountAction('upload_media')
        ->set('mountedActions.0.files', [
            $transcript,
        ])
        ->callMountedAction()
        ->assertHasActionErrors([
            'files' => 'Cannot upload transcripts without corresponding media files.',
        ])
        ->assertNoRedirect();
});
