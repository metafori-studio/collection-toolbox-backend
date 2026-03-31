<?php

namespace Metafori\Etno\Tests\Feature\Filament\Actions\Items;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Metafori\Core\Models\User;
use Metafori\Etno\Filament\Resources\Items\Pages\EditItem;
use Metafori\Etno\Filament\Resources\Items\RelationManagers\MediaRelationManager;
use Metafori\Etno\Jobs\Items\ProcessMediaUploadJob;
use Metafori\Etno\Models\Item;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    Queue::fake();
    actingAs(User::factory()->admin()->create());
});

it('dispatches process media upload jobs when media files are uploaded', function () {
    $item = Item::factory()->create();

    $files = [
        UploadedFile::fake()->image('image1.jpg'),
        UploadedFile::fake()->create('audio.mp3', 100, 'audio/mpeg'),
    ];

    livewire(MediaRelationManager::class, [
        'ownerRecord' => $item,
        'pageClass' => EditItem::class,
    ])
        ->mountAction('upload_media')
        ->fillForm(['attachments' => $files])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    Queue::assertPushed(ProcessMediaUploadJob::class, 2);
});

it('displays a notice on the relation manager when there are pending media upload jobs', function () {
    $item = Item::factory()->create();

    $files = [
        UploadedFile::fake()->image('image1.jpg'),
        UploadedFile::fake()->create('audio.mp3', 100, 'audio/mpeg'),
    ];

    livewire(MediaRelationManager::class, [
        'ownerRecord' => $item,
        'pageClass' => EditItem::class,
    ])
        ->mountAction('upload_media')
        ->fillForm(['attachments' => $files])
        ->callMountedAction()
        ->assertSee('2 media files are currently being processed in the background. They will appear here automatically when finished.');
});
