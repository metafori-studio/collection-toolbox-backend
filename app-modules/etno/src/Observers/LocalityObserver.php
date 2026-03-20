<?php

namespace Metafori\Etno\Observers;

use Metafori\Core\Models\Contracts\Locality;
use Metafori\Etno\Repositories\ItemRepository;

class LocalityObserver
{
    public function __construct(public ItemRepository $repository) {}

    public function saved(Locality $locality): void
    {
        /** @var \Illuminate\Database\Eloquent\Model $locality */
        if ($locality->wasChanged('latitude') || $locality->wasChanged('longitude')) {
            $this->repository->invalidateMapPointsCache();
        }
    }

    public function deleted(Locality $locality): void
    {
        /** @var \Illuminate\Database\Eloquent\Model $locality */
        if ($locality->latitude !== null && $locality->longitude !== null) {
            $this->repository->invalidateMapPointsCache();
        }
    }

    public function restored(Locality $locality): void
    {
        /** @var \Illuminate\Database\Eloquent\Model $locality */
        if ($locality->latitude !== null && $locality->longitude !== null) {
            $this->repository->invalidateMapPointsCache();
        }
    }
}
