<?php

namespace Metafori\Archeo\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Metafori\Archeo\Http\Resources\ActivityResource;
use Metafori\Archeo\Models\Activity;

class ActivityController extends Controller
{
    /**
     * Display the specified activity by its activity number.
     */
    public function show(string $activityNumber): ActivityResource
    {
        $activity = Activity::query()
            ->where('activity_number', $activityNumber)
            ->with(['galleries.media'])
            ->firstOrFail();

        return new ActivityResource($activity);
    }
}
