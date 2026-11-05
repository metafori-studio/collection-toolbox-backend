<?php

namespace Metafori\Art\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Metafori\Art\Models\Artwork;
use Metafori\Art\Support\FacetMetadata;
use OpenSearch\Client;
use Stringable;

class ArtworkRepository
{
    public function findOrFail(string $slug): Artwork
    {
        return Artwork::query()
            ->with([
                'authors.person',
                'collections' => fn ($query) => $query->withCount('artworks'),
                'media',
            ])
            ->where('slug', $slug)
            ->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @param  array<string, 'asc'|'desc'>  $sorts
     */
    public function paginate(array $filters, array $sorts, string $locale, int $perPage = 15): LengthAwarePaginator
    {
        $creationDateFilter = $this->buildDateFilter(
            Arr::pull($filters, 'creation_date_from'),
            Arr::pull($filters, 'creation_date_to'),
        );

        $authorFilters = Arr::only($filters, ['author.person_id', 'author.label']);
        $filters = Arr::except($filters, ['author.person_id', 'author.label']);

        $query = Artwork::search('*', function (Client $client, string $query, array $params) use ($creationDateFilter, $authorFilters) {
            if ($creationDateFilter) {
                $params['body']['query']['bool']['filter']['bool']['must'][]['range']['creation_date'] = $creationDateFilter;
            }

            if ($authorFilters !== []) {
                $params['body']['query']['bool']['filter']['bool']['must'][] = $this->authorFilter($authorFilters);
            }

            return $client->search($params);
        });

        foreach ($filters as $field => $value) {
            $query->whereIn($field, (array) $value);
        }

        foreach ($sorts as $field => $dir) {
            $query->orderBy($this->resolveSortField($field, $locale), $dir);
        }

        $query->query(function ($eloquentQuery) {
            $eloquentQuery->with([
                'authors.person',
                'collections' => fn ($query) => $query->withCount('artworks'),
                'media',
            ]);
        });

        return $query->paginate($perPage);
    }

    public function search(string $query, int $size, string $locale): EloquentCollection
    {
        $textFields = [
            "title.{$locale}",
            "description.{$locale}",
        ];

        $suggestFields = [
            "title.{$locale}.suggest",
        ];

        $scoutQuery = Artwork::search($query, function (Client $client, string $q, array $params) use ($textFields, $suggestFields) {
            $params['body']['query'] = [
                'bool' => [
                    'should' => [
                        [
                            'multi_match' => [
                                'query' => $q,
                                'fields' => $textFields,
                            ],
                        ],
                        [
                            'multi_match' => [
                                'query' => $q,
                                'type' => 'bool_prefix',
                                'fields' => $suggestFields,
                            ],
                        ],
                    ],
                    'minimum_should_match' => 1,
                ],
            ];

            return $client->search($params);
        });

        $scoutQuery->query(function ($eloquentQuery) {
            $eloquentQuery->with([
                'authors.person',
                'collections' => fn ($query) => $query->withCount('artworks'),
                'media',
            ]);
        });

        return $scoutQuery->take($size)->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<string, mixed>
     */
    public function aggregations(array $filters, int $size = 100): Collection
    {
        $creationDateFilter = $this->buildDateFilter(
            Arr::pull($filters, 'creation_date_from'),
            Arr::pull($filters, 'creation_date_to'),
        );

        $aggs = collect(FacetMetadata::all())
            ->mapWithKeys(fn (string $field) => [
                $field => collect(Arr::except($filters, in_array($field, ['author.person_id', 'author.label'], true)
                    ? ['author.person_id', 'author.label']
                    : [$field]))
                    ->except(['author.person_id', 'author.label'])
                    ->map(fn (array $values, string $key) => [
                        'terms' => [$key => $values],
                    ])
                    ->when(! in_array($field, ['author.person_id', 'author.label'], true) && Arr::only($filters, ['author.person_id', 'author.label']) !== [],
                        fn ($activeFilters) => $activeFilters->push($this->authorFilter(Arr::only($filters, ['author.person_id', 'author.label'])))
                    )
                    ->concat($creationDateFilter ? [[
                        'range' => ['creation_date' => $creationDateFilter],
                    ]] : [])
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

        $query = Artwork::search('*', function (Client $client, string $query, array $params) use ($aggs) {
            $params['body']['aggs'] = $aggs;
            $params['body']['size'] = 0;

            return $client->search($params);
        });

        $aggregations = $query->raw()['aggregations'] ?? [];

        return $this->formatAggregations($aggregations);
    }

    /**
     * @param  array<string, mixed>  $aggregations
     * @return Collection<string, mixed>
     */
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
                        $key === 'author.label' => $bucket['key'],
                        default => null,
                    },
                    'count' => $bucket['doc_count'],
                ])
                ->filter(fn (array $bucket) => $bucket['label'] !== null)
                ->values()
            );
    }

    /**
     * @param  array<string, array<int, mixed>>  $filters
     * @return array<string, mixed>
     */
    protected function authorFilter(array $filters): array
    {
        return [
            'bool' => [
                'should' => collect($filters)->map(fn (array $values, string $field) => [
                    'terms' => [$field => $values],
                ])->values()->all(),
                'minimum_should_match' => 1,
            ],
        ];
    }

    protected function resolveSortField(string $field, string $locale): string
    {
        if ($field === 'title') {
            return "title.{$locale}.keyword";
        }

        return $field;
    }

    /**
     * @return array<string, string>
     */
    protected function buildDateFilter(mixed $from, mixed $to): array
    {
        return array_filter([
            'gte' => is_numeric($from) ? "{$from}-01-01" : $from,
            'lte' => is_numeric($to) ? "{$to}-12-31" : $to,
        ]);
    }

    public function refreshIndex(): void
    {
        app(Client::class)->indices()->refresh(['index' => (new Artwork)->searchableAs()]);
    }
}
