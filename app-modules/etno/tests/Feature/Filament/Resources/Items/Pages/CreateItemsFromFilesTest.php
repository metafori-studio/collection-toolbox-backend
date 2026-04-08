<?php

namespace Metafori\Etno\Tests\Feature\Filament\Resources\Items\Pages;

use Illuminate\Http\UploadedFile;
use Metafori\Core\Models\User;
use Metafori\Etno\Filament\Resources\Items\Pages\CreateItemsFromFiles;
use Metafori\Etno\Models\Document;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::factory()->admin()->create());
});

it('extracts transcripts, syncs items and applies transcripts', function () {
    $document = Document::factory()->create(['id' => 'AA000001']);

    $image1 = UploadedFile::fake()->create('test1.jpg', 100, 'image/jpeg');
    $transcript1 = UploadedFile::fake()->create('test1.txt', 'transcript one text', 'text/plain');
    $image2 = UploadedFile::fake()->create('test2.jpg', 100, 'image/jpeg');

    livewire(CreateItemsFromFiles::class, [
        'parentRecord' => $document,
    ])
        ->fillForm([
            'files' => [$image1, $transcript1, $image2],
        ])
        ->goToNextWizardStep()
        ->call('createFromFiles')
        ->assertHasNoFormErrors();

    $items = $document->items()->get();
    expect($items)->toHaveCount(2);

    $item1 = $items->firstWhere('identifier', 'AA000001:a');
    $item2 = $items->firstWhere('identifier', 'AA000001:b');

    expect($item1->media)->toHaveCount(1);
    expect($item1->media->first()->custom_properties['transcripts']['txt'])->toBe('transcript one text');
    expect($item2->media)->toHaveCount(1);
    expect($item2->media->first()->custom_properties['transcripts']['txt'])->toBeNull();
});

it('can reorder items by file name', function () {
    $document = Document::factory()->create(['id' => 'AA000001']);

    $image1 = UploadedFile::fake()->create('z_test.jpg', 100, 'image/jpeg');
    $image2 = UploadedFile::fake()->create('a_test.jpg', 100, 'image/jpeg');

    $livewire = livewire(CreateItemsFromFiles::class, [
        'parentRecord' => $document,
    ])
        ->fillForm([
            'files' => [$image1, $image2],
        ])
        ->goToNextWizardStep();

    $itemsState = $livewire->instance()->form->getState()['items'];
    $keys = array_keys($itemsState);

    expect($itemsState[$keys[0]]['media']['file']->getClientOriginalName())->toBe('z_test.jpg')
        ->and($itemsState[$keys[1]]['media']['file']->getClientOriginalName())->toBe('a_test.jpg');

    $livewire->callFormComponentAction('items', 'order_by_name');

    $itemsState = $livewire->instance()->form->getState()['items'];
    $keys = array_keys($itemsState);

    expect($itemsState[$keys[0]]['media']['file']->getClientOriginalName())->toBe('a_test.jpg')
        ->and($itemsState[$keys[1]]['media']['file']->getClientOriginalName())->toBe('z_test.jpg');
});
