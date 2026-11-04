<?php

namespace Metafori\Etno\Observers;

use Metafori\Etno\Models\Item;
use Metafori\Etno\Models\Media;
use Metafori\Etno\Support\ItemSearchSynchronizer;

class MediaObserver
{
    public function __construct(protected ItemSearchSynchronizer $searchSynchronizer) {}

    public function saved(Media $media): void
    {
        $this->syncItem($media);
    }

    public function deleted(Media $media): void
    {
        $this->syncItem($media);
    }

    protected function syncItem(Media $media): void
    {
        if ($media->model instanceof Item) {
            $this->searchSynchronizer->syncItem($media->model);
        }
    }
}
