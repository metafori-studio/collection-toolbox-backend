<?php

namespace Metafori\Archeo\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Metafori\Archeo\Http\Resources\ActivityResource;
use Metafori\Archeo\Models\Activity;

class ActivityController extends Controller
{
    /**
     * Display a paginated listing of the activities.
     */
    public function index(Request $request)
    {
        $activities = Activity::query()
            ->paginate($request->integer('per_page', 15));

        return ActivityResource::collection($activities);
    }

    /**
     * Display the specified activity detail.
     */
    public function show(Activity $activity)
    {
        return new ActivityResource($activity);
    }

    /**
     * Display a non-paginated listing of map points.
     */
    public function mapPoints()
    {
        $activities = Activity::query()
            ->whereNotNull('coordinate_x')
            ->whereNotNull('coordinate_y')
            ->get(['activity_number', 'coordinate_x', 'coordinate_y', 'localization_degree']);

        return response()->json([
            'data' => $activities->map(fn ($activity) => [
                'id' => $activity->activity_number,
                'coordinate_x' => $activity->coordinate_x,
                'coordinate_y' => $activity->coordinate_y,
                'localization_degree' => $activity->localization_degree,
            ]),
        ]);
    }
}
