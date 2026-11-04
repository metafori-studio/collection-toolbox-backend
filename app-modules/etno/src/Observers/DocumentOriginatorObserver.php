<?php

namespace Metafori\Etno\Observers;

use Metafori\Etno\Models\DocumentOriginator;
use Metafori\Etno\Support\ItemSearchSynchronizer;

class DocumentOriginatorObserver
{
    public function __construct(protected ItemSearchSynchronizer $searchSynchronizer) {}

    public function saved(DocumentOriginator $originator): void
    {
        $this->syncItems($originator);
    }

    public function deleted(DocumentOriginator $originator): void
    {
        $this->syncItems($originator);
    }

    protected function syncItems(DocumentOriginator $originator): void
    {
        if ($originator->document !== null) {
            $this->searchSynchronizer->syncDocumentItems($originator->document);
        }
    }
}
