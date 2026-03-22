<?php

namespace Metafori\Etno\Observers;

use Metafori\Etno\Models\Document;
use Metafori\Etno\Repositories\ItemRepository;

class DocumentObserver
{
    public function __construct(public ItemRepository $repository) {}

    public function updated(Document $document): void
    {
        if ($document->wasChanged('locality_id') || $document->wasChanged('locality_type')) {
            $this->repository->invalidateMapPointsCache();
        }
    }

    public function deleted(Document $document): void
    {
        if ($document->locality_id !== null) {
            $this->repository->invalidateMapPointsCache();
        }
    }

    public function restored(Document $document): void
    {
        if ($document->locality_id !== null) {
            $this->repository->invalidateMapPointsCache();
        }
    }
}
