<?php

use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Repositories\ItemRepository;
use Metafori\Opensearch\Testing\RefreshIndices;

uses(RefreshIndices::class);

it('synchronizes item search indexing when a document is deleted and restored', function () {
    $document = Document::factory()->create();
    $item = Item::factory()->for($document, 'document')->create();

    app(ItemRepository::class)->refreshIndex();

    $resultsBefore = Item::search()->raw();
    expect($resultsBefore['hits']['total']['value'])->toBe(1);

    $document->delete();

    app(ItemRepository::class)->refreshIndex();

    $resultsAfter = Item::search()->raw();
    expect($resultsAfter['hits']['total']['value'])->toBe(0);

    $document->restore();

    app(ItemRepository::class)->refreshIndex();

    $resultsRestored = Item::search()->raw();
    expect($resultsRestored['hits']['total']['value'])->toBe(1);
});
