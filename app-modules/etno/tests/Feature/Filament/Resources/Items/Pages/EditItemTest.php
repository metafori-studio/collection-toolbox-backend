<?php

namespace Metafori\Etno\Tests\Feature\Filament\Resources\Items\Pages;

use Metafori\Core\Models\User;
use Metafori\Etno\Filament\Resources\Items\Pages\EditItem;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Support\Frontend;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;

beforeEach(function () {
    actingAs(User::factory()->admin()->create());
});

it('shows view_frontend action and hides unpublished text when item is published', function () {
    $item = Item::factory()->published(true)->create();

    $expectedUrl = Frontend::itemUrl($item->identifier);

    livewire(EditItem::class, [
        'parentRecord' => $item->document,
        'record' => $item->id,
    ])
        ->assertActionVisible('view_frontend')
        ->assertActionHasUrl('view_frontend', $expectedUrl)
        ->assertActionShouldOpenUrlInNewTab('view_frontend')
        ->assertActionHidden('unpublished');
});

it('shows unpublished text and hides view_frontend action when item is unpublished', function () {
    $item = Item::factory()->published(false)->create();

    livewire(EditItem::class, [
        'parentRecord' => $item->document,
        'record' => $item->id,
    ])
        ->assertActionVisible('unpublished')
        ->assertActionHidden('view_frontend');
});
