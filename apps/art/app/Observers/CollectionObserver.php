<?php

namespace Metafori\Art\Observers;

use Metafori\Art\Models\Artwork;
use Metafori\Art\Models\Collection;

class CollectionObserver
{
    public function saved(Collection $collection): void
    {
        if ($collection->wasChanged('published_at')) {
            foreach (Artwork::query()->whereHas('collections', fn ($query) => $query->whereKey($collection->getKey()))->lazyById(100) as $artwork) {
                $artwork->searchable();
            }
        }
    }
}
