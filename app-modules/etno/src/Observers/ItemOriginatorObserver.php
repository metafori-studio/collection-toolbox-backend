<?php

namespace Metafori\Etno\Observers;

use Metafori\Etno\Models\ItemOriginator;
use Metafori\Etno\Support\ItemSearchSynchronizer;

class ItemOriginatorObserver
{
    public function __construct(protected ItemSearchSynchronizer $searchSynchronizer) {}

    public function deleted(ItemOriginator $originator): void
    {
        $item = $originator->item;

        if ($item !== null) {
            $this->searchSynchronizer->syncItem($item);
        }
    }
}
