<?php

namespace Metafori\Etno\Support;

use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\Item;

class ItemSearchSynchronizer
{
    public function syncItem(Item $item): void
    {
        if ($item->shouldBeSearchable()) {
            $item->searchable();
        } else {
            $item->unsearchable();
        }
    }

    public function syncDocumentItems(Document $document): void
    {
        $items = $document->items()->get();
        [$searchable, $unsearchable] = $items->partition->shouldBeSearchable();

        $searchable->searchable();
        $unsearchable->unsearchable();
    }
}
