<?php

namespace Metafori\Etno\Tests\Feature\Filament\Items\RelationManagers;

use Filament\Actions\Testing\TestAction;
use Illuminate\Http\UploadedFile;
use Metafori\Core\Models\User;
use Metafori\Etno\Filament\Resources\Items\Pages\EditItem;
use Metafori\Etno\Filament\Resources\Items\RelationManagers\MediaRelationManager;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\Item;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $this->user = User::factory()->admin()->create();
    actingAs($this->user);
});

it('can edit media', function () {
    $item = Item::factory()->create();
    $media = $item->addMedia(UploadedFile::fake()->create('test.jpg'))->toMediaCollection();

    livewire(MediaRelationManager::class, [
        'ownerRecord' => $item,
        'pageClass' => EditItem::class,
    ])
        ->callAction(TestAction::make('edit')->table($media), [
            'name' => 'New Name',
            'custom_properties' => [
                'transcripts' => [
                    'xml' => '<?xml version="1.0" encoding="UTF-8"?>',
                    'txt' => 'text',
                ],
            ],
        ])
        ->assertHasNoErrors();

    expect($media->refresh()->name)->toBe('New Name')
        ->and($media->custom_properties['transcripts']['xml'])->toBe('<?xml version="1.0" encoding="UTF-8"?>')
        ->and($media->custom_properties['transcripts']['txt'])->toBe('text');
});

it('can delete media', function () {
    $item = Item::factory()->create();

    $media = $item->addMedia(UploadedFile::fake()->create('test.jpg'))->toMediaCollection();

    livewire(MediaRelationManager::class, [
        'ownerRecord' => $item,
        'pageClass' => EditItem::class,
    ])
        ->callAction(TestAction::make('delete')->table($media));

    expect(Media::find($media->id))->toBeNull();
});

it('can bulk delete media', function () {
    $document = Document::factory()->create();
    $item = Item::factory()->create(['document_id' => $document->id]);

    $media = [
        $item->addMedia(UploadedFile::fake()->create('test1.jpg'))->toMediaCollection(),
        $item->addMedia(UploadedFile::fake()->create('test2.jpg'))->toMediaCollection(),
    ];

    livewire(MediaRelationManager::class, [
        'ownerRecord' => $item,
        'pageClass' => EditItem::class,
    ])
        ->selectTableRecords($media)
        ->callAction(TestAction::make('delete')->table()->bulk());

    expect(Media::find($media[0]->id))->toBeNull()
        ->and(Media::find($media[1]->id))->toBeNull();
});
