<?php

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\Item;

beforeEach(function () {
    $this->document = Document::factory()->create();
});

it('throws exception when creating an item with a duplicated suffix', function () {
    Item::factory()
        ->for($this->document)
        ->create(['suffix' => 'AAA']);

    Item::factory()
        ->for($this->document)
        ->create(['suffix' => 'AAA']);

})->throws(UniqueConstraintViolationException::class);

it('does not throw exception when creating and soft-deleting an item with a duplicated suffix at different time', function () {
    Item::factory()
        ->for($this->document)
        ->create(['suffix' => 'AAA'])
        ->delete();

    Carbon::setTestNow(now()->addSecond());

    Item::factory()
        ->for($this->document)
        ->create(['suffix' => 'AAA'])
        ->delete();

})->throwsNoExceptions();

it('throws exception when restoring an item with a duplicated suffix', function () {
    $item = Item::factory()
        ->for($this->document)
        ->create(['suffix' => 'AAA']);

    $item->delete();

    Item::factory()
        ->for($this->document)
        ->create(['suffix' => 'AAA']);

    $item->restore();

})->throws(UniqueConstraintViolationException::class);
