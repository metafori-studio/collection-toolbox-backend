<?php

namespace Metafori\Archeo\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Metafori\Archeo\Http\Requests\Api\ActivityAggregationsRequest;
use Metafori\Archeo\Http\Requests\Api\ActivityIndexRequest;
use Metafori\Archeo\Http\Resources\ActivityMapPointResource;
use Metafori\Archeo\Http\Resources\ActivityResource;
use Metafori\Archeo\Jobs\WatermarkPdfJob;
use Metafori\Archeo\Models\Activity;

class ActivityController extends Controller
{
    protected const SORTABLE = [
        'created_at',
        'activity_year_start',
        'activity_year_end',
        'activity_type',
        'activity_number',
        'municipality',
        'district',
        'cadastral_area',
        'institution',
        'research_leader',
        'registration_year',
        'size_category',
    ];

    protected const FILTERABLE = [
        'activity_type',
        'activity_number',
        'activity_year_start',
        'activity_year_end',
        'municipality',
        'district',
        'cadastral_area',
        'institution',
        'research_leader',
        'registration_year',
        'size_category',
        'author_ns',
        'dating_ns',
        'dating_ceans',
        'dating_site_type',
    ];

    /**
     * Display a listing of activities.
     */
    public function index(ActivityIndexRequest $request): AnonymousResourceCollection
    {
        $query = Activity::query()->with(['galleries.media']);

        $this->applyFilters($query, $request->validated('filter', []));
        $this->applySorts($query, $request->sorts());

        $perPage = $request->query('per_page', 15);

        return ActivityResource::collection($query->paginate($perPage));
    }

    /**
     * Display aggregations for activities.
     */
    public function aggregations(ActivityAggregationsRequest $request): JsonResponse
    {
        $fields = [
            'activity_type',
            'municipality',
            'district',
            'cadastral_area',
            'institution',
            'research_leader',
            'size_category',
            'activity_year_start',
            'activity_year_end',
            'registration_year',
            'author_ns',
            'dating_ns',
            'dating_ceans',
            'dating_site_type',
        ];

        $filters = $request->validated('filter', []);
        $data = [];

        foreach ($fields as $field) {
            $query = Activity::query();

            // Apply filters except for the current field to allow selecting other options in the same facet
            $otherFilters = collect($filters)->except($field)->toArray();
            $this->applyFilters($query, $otherFilters);

            if (in_array($field, ['author_ns', 'dating_ns', 'dating_ceans', 'dating_site_type'], true)) {
                $buckets = $query
                    ->selectRaw("jsonb_array_elements_text({$field}) as value")
                    ->whereNotNull($field)
                    ->groupByRaw("jsonb_array_elements_text({$field})")
                    ->selectRaw('count(*) as doc_count')
                    ->orderByDesc('doc_count')
                    ->limit(100)
                    ->get()
                    ->map(fn ($row) => [
                        'value' => $row->value,
                        'label' => (string) $row->value,
                        'count' => $row->doc_count,
                    ]);
            } else {
                $buckets = $query->select($field)
                    ->whereNotNull($field)
                    ->groupBy($field)
                    ->selectRaw('count(*) as doc_count')
                    ->orderByDesc('doc_count')
                    ->limit(100)
                    ->get()
                    ->map(fn ($row) => [
                        'value' => $row->$field,
                        'label' => (string) $row->$field,
                        'count' => $row->doc_count,
                    ]);
            }

            $data[$field] = $buckets;
        }

        return response()->json([
            /** @var array<string, array<array{value: string, label: string, count: int}>> */
            'data' => $data,
        ]);
    }

    /**
     * Display the specified activity by its activity number.
     */
    public function show(Request $request, string $activityNumber): ActivityResource
    {
        $activity = Activity::query()
            ->where('activity_number', $activityNumber)
            ->with(['galleries.media', 'media'])
            ->firstOrFail();

        if (config('archeo.watermark_image') && file_exists(config('archeo.watermark_image'))) {
            $activity->getMedia('pdfs')
                ->reject(fn ($pdf) => $pdf->hasGeneratedConversion('watermarked'))
                ->each(fn ($pdf) => WatermarkPdfJob::dispatch($pdf->id, $request->user()));
        }

        return new ActivityResource($activity);
    }

    /**
     * Display map points for activities.
     */
    public function mapPoints(): AnonymousResourceCollection
    {
        $activities = Cache::rememberForever(
            Activity::MAP_POINTS_CACHE_KEY,
            Activity::query()
                ->select(['activity_number', 'latitude', 'longitude', 'localization_degree'])
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get(...)
        );

        return ActivityMapPointResource::collection($activities);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        foreach ($filters as $field => $values) {
            if (empty($values) || ! in_array($field, self::FILTERABLE, true)) {
                continue;
            }

            $values = array_filter($values, 'is_scalar');

            if (empty($values)) {
                continue;
            }

            if (in_array($field, ['author_ns', 'dating_ns', 'dating_ceans', 'dating_site_type'], true)) {
                $query->where(function (Builder $q) use ($field, $values) {
                    foreach ($values as $value) {
                        $q->orWhereJsonContains($field, $value);
                    }
                });
            } else {
                $query->whereIn($field, $values);
            }
        }
    }

    protected function applySorts(Builder $query, array $sorts): void
    {
        if (empty($sorts)) {
            $query->latest();

            return;
        }

        foreach ($sorts as $field => $dir) {
            if (! in_array($field, self::SORTABLE, true)) {
                continue;
            }

            $query->orderBy($field, strtolower($dir) === 'desc' ? 'desc' : 'asc');
        }
    }
}
