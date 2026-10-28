<?php

namespace Metafori\Etno\Observers;

use Metafori\Etno\Models\Document;
use Metafori\Etno\Repositories\ItemRepository;
use Metafori\Etno\Support\ItemSearchSynchronizer;

class DocumentObserver
{
    public function __construct(public ItemRepository $repository, protected ItemSearchSynchronizer $searchSynchronizer) {}

    public function updated(Document $document): void
    {
        if ($document->wasChanged(['locality_id', 'locality_type', 'access_rights'])) {
            $this->repository->invalidateMapPointsCache();
        }

        $this->searchSynchronizer->syncDocumentItems($document);
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

        $this->searchSynchronizer->syncDocumentItems($document);
    }
}
