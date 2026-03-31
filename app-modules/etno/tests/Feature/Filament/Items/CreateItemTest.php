<?php

namespace Metafori\Etno\Tests\Feature\Filament\Items;

use Metafori\Core\Models\User;
use Metafori\Etno\Filament\Resources\Items\Pages\CreateItem;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\Item;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $user = User::factory()->admin()->create();
    actingAs($user);
});

it('cannot create item with duplicate identifier', function () {
    $document = Document::factory()->create();
    Item::factory()->for($document)->create(['suffix' => 'AAA']);

    livewire(CreateItem::class, [
        'parentRecord' => $document,
    ])
        ->fillForm(['suffix' => 'AAA'])
        ->call('create')
        ->assertHasFormErrors(['suffix']);
});

it('can create item with duplicate soft-deleted identifier', function () {
    $document = Document::factory()->create();
    Item::factory()->for($document)->create(['suffix' => 'AAA'])
        ->delete();

    livewire(CreateItem::class, [
        'parentRecord' => $document,
    ])
        ->fillForm(['suffix' => 'AAA'])
        ->call('create')
        ->assertHasNoFormErrors();
});

it('can create item with same suffix on different document', function () {
    $document1 = Document::factory()->create();
    $document2 = Document::factory()->create();

    Item::factory()->for($document1)->create(['suffix' => 'AAA']);

    livewire(CreateItem::class, [
        'parentRecord' => $document2,
    ])
        ->fillForm(['suffix' => 'AAA'])
        ->call('create')
        ->assertHasNoFormErrors();
});
