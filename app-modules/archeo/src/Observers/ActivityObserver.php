<?php

namespace Metafori\Archeo\Observers;

use Illuminate\Support\Facades\Cache;
use Metafori\Archeo\Models\Activity;

class ActivityObserver
{
    public function saved(Activity $activity): void
    {
        if ($activity->wasChanged(['latitude', 'longitude'])) {
            $this->invalidateMapPointsCache();
        }
    }

    public function deleted(Activity $activity): void
    {
        $this->invalidateMapPointsCache();
    }

    protected function invalidateMapPointsCache(): void
    {
        Cache::forget(Activity::MAP_POINTS_CACHE_KEY);
    }
}
