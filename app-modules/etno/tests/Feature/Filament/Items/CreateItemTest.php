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
    $item = Item::factory()->for($document)->create();

    livewire(CreateItem::class, [
        'parentRecord' => $document,
    ])
        ->fillForm([
            'suffix' => $item->suffix,
        ])
        ->call('create')
        ->assertHasFormErrors(['suffix']);
});

it('can create item with duplicate soft-deleted identifier', function () {
    $document = Document::factory()->create();
    $item = Item::factory()->for($document)->create();
    $item->delete();

    livewire(CreateItem::class, [
        'parentRecord' => $document,
    ])
        ->fillForm([
            'suffix' => $item->suffix,
        ])
        ->call('create')
        ->assertHasNoFormErrors();
});
