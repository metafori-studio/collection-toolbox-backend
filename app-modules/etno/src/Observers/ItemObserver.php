<?php

namespace Metafori\Etno\Observers;

use Metafori\Etno\Models\Item;
use Metafori\Etno\Repositories\ItemRepository;

class ItemObserver
{
    public function __construct(public ItemRepository $repository) {}

    public function created(Item $item): void
    {
        if ($item->locality_id !== null) {
            $this->repository->invalidateMapPointsCache();
        }
    }

    public function updated(Item $item): void
    {
        if ($item->wasChanged(['locality_id', 'locality_type', 'access_rights', 'document_overrides'])) {
            $this->repository->invalidateMapPointsCache();
        }
    }

    public function deleted(Item $item): void
    {
        if ($item->locality_id !== null) {
            $this->repository->invalidateMapPointsCache();
        }
    }

    public function restored(Item $item): void
    {
        if ($item->locality_id !== null) {
            $this->repository->invalidateMapPointsCache();
        }
    }
}
