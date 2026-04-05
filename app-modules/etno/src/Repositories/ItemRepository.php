<?php

namespace Metafori\Etno\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Support\FacetMetadata;
use OpenSearch\Client;
use Stringable;

class ItemRepository
{
    protected const string MAP_POINTS_CACHE_KEY = 'etno.item.map-points';

    public function findOrFail(string $id): Item
    {
        return Item::query()
            ->with(Item::relations())
            ->where('identifier', $id)
            ->firstOrFail();
    }

    public function paginate(array $filters = [], array $sorts = []): LengthAwarePaginator
    {
        $timePeriodFilter = $this->buildDateFilter(
            Arr::pull($filters, 'time_period_from'),
            Arr::pull($filters, 'time_period_to'),
        );

        $query = Item::search('*', function (Client $client, string $query, array $params) use ($timePeriodFilter) {
            if ($timePeriodFilter) {
                $params['body']['query']['bool']['filter']['bool']['must'][]['range']['time_period'] = $timePeriodFilter;
            }

            return $client->search($params);
        });

        foreach ($filters as $field => $value) {
            $query->whereIn($field, $value);
        }

        foreach ($sorts as $field => $dir) {
            $query->orderBy($field, $dir);
        }

        $query->query(function ($eloquentQuery) {
            $eloquentQuery->with(Item::documentRelations([
                'authors',
                'researchers',
                'originators.person',
                ...Item::localityRelations(),
            ]));
        });

        $perPage = request()->query('per_page', 15);

        return $query->paginate($perPage);
    }

    public function aggregations(array $filters = [], int $size = 1000): Collection
    {
        $timePeriodFilter = $this->buildDateFilter(
            Arr::pull($filters, 'time_period_from'),
            Arr::pull($filters, 'time_period_to'),
        );

        $aggs = collect(FacetMetadata::all())
            ->mapWithKeys(fn (string $field) => [
                $field => collect($filters)
                    ->except($field)
                    ->map(fn (array $values, string $key) => [
                        'terms' => [$key => $values],
                    ])
                    ->when($timePeriodFilter, fn ($filters) => $filters->push([
                        'range' => ['time_period' => $timePeriodFilter],
                    ]))
                    ->values(),
            ])
            ->map(fn (Collection $activeFilters, string $field) => [
                'filter' => $activeFilters->isEmpty()
                    ? ['match_all' => new \stdClass]
                    : ['bool' => ['filter' => $activeFilters]],
                'aggs' => [
                    'filtered' => ['terms' => ['field' => $field, 'size' => $size]],
                ],
            ])
            ->toArray();

        $query = Item::search('*', function (Client $client, string $query, array $params) use ($aggs) {
            $params['body']['aggs'] = $aggs;
            $params['body']['size'] = 0;

            return $client->search($params);
        });

        $aggregations = $query->raw()['aggregations'] ?? [];

        return $this->formatAggregations($aggregations);
    }

    protected function formatAggregations(array $aggregations): Collection
    {
        $modelMapping = FacetMetadata::MODEL_MAPPING;

        $enumMapping = FacetMetadata::ENUM_MAPPING;

        $parsedAggregations = collect($aggregations)
            ->mapWithKeys(fn (array $agg, string $key) => [$key => $agg['filtered'] ?? $agg]);

        $modelLabels = $parsedAggregations
            ->filter(fn (array $agg) => isset($agg['buckets']))
            ->only(array_keys($modelMapping))
            ->map(fn (array $agg, string $key) => $modelMapping[$key]::query()
                ->whereIn('id', array_column($agg['buckets'], 'key'))
                ->get()
                ->keyBy('id')
                ->map(fn (Stringable $model) => (string) $model)
            );

        return $parsedAggregations
            ->map(fn (array $agg, string $key) => collect($agg['buckets'])
                ->map(fn (array $bucket) => [
                    'value' => $bucket['key'],
                    'label' => match (true) {
                        isset($modelMapping[$key]) => ($modelLabels[$key] ?? collect())->get($bucket['key']),
                        isset($enumMapping[$key]) => $enumMapping[$key]::tryFrom($bucket['key'])?->getLabel(),
                        default => null,
                    },
                    'count' => $bucket['doc_count'],
                ])
                ->filter(fn (array $bucket) => $bucket['label'] !== null)
                ->values()
            );
    }

    public function mapPoints(): EloquentCollection
    {
        $with = [
            'locality' => fn (MorphTo $query) => $query->select([
                'id',
                'latitude',
                'longitude',
            ]),
        ];

        return Cache::rememberForever(
            self::MAP_POINTS_CACHE_KEY,
            Item::query()
                ->select([
                    'document_id',
                    'suffix',
                    'identifier',
                    'locality_id',
                    'locality_type',
                    'document_overrides',
                ])
                ->with(Item::documentRelations($with, fn (BelongsTo $belongsTo) => $belongsTo->select([
                    'id',
                    'locality_id',
                    'locality_type',
                ])))
                ->orderBy('id')
                ->get(...)
        );
    }

    public function invalidateMapPointsCache(): void
    {
        Cache::forget(self::MAP_POINTS_CACHE_KEY);
    }

    public function refreshIndex(): void
    {
        app(Client::class)->indices()->refresh(['index' => (new Item)->searchableAs()]);
    }

    protected function buildDateFilter(mixed $from, mixed $to): array
    {
        return \array_filter([
            'gte' => \is_numeric($from) ? "{$from}||/y" : $from,
            'lte' => \is_numeric($to) ? "{$to}||/y" : $to,
        ]);
    }
}
