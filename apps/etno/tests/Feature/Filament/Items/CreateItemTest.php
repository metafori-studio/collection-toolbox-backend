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

it('can create item with duplicate soft-deleted identifier', function () {
    $document = Document::factory()->create();
    Item::factory()->for($document)->create(['suffix' => 'AAA'])
        ->delete();

    livewire(CreateItem::class, [
        'parentRecord' => $document,
    ])
        ->set('data.suffix', 'AAA')
        ->call('create')
        ->assertHasNoFormErrors();

    // Verify it was actually saved (withTrashed because one item is soft-deleted)
    expect(Item::withTrashed()->count())->toBe(2);
});
