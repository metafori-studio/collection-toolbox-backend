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

        $document->items()->searchable();
    }

    public function deleted(Document $document): void
    {
        $shouldInvalidate = $document->locality_id !== null
            || $document->items()->has('locality')->exists();

        if ($shouldInvalidate) {
            $this->repository->invalidateMapPointsCache();
        }

        $document->items()
            ->withoutGlobalScope('document')
            ->unsearchable();
    }

    public function restored(Document $document): void
    {
        $shouldInvalidate = $document->locality_id !== null
            || $document->items()->has('locality')->exists();

        if ($shouldInvalidate) {
            $this->repository->invalidateMapPointsCache();
        }

        $document->items()->searchable();
    }
}
