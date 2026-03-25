<?php

namespace Metafori\Etno\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Metafori\Core\Enums\Language;
use Metafori\Core\Enums\License;
use Metafori\Core\Models\Country;
use Metafori\Core\Models\District;
use Metafori\Core\Models\Keyword;
use Metafori\Core\Models\Location;
use Metafori\Core\Models\Municipality;
use Metafori\Core\Models\MunicipalityPart;
use Metafori\Core\Models\Organization;
use Metafori\Core\Models\Person;
use Metafori\Core\Models\Region;
use Metafori\Etno\Enums\AccessRights;
use Metafori\Etno\Enums\AccrualMethod;
use Metafori\Etno\Enums\CollectionMethod;
use Metafori\Etno\Enums\ItemType;
use Metafori\Etno\Enums\ProductionMethod;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Models\Project;
use Metafori\Etno\Models\ResearchCollection;
use OpenSearch\Client;
use Stringable;

class ItemRepository
{
    protected const string MAP_POINTS_CACHE_KEY = 'etno.item.map-points';

    public function findOrFail(string $id): Item
    {
        return Item::query()
            ->with(Item::relations())
            ->findOrFail($id);
    }

    public function paginate(array $filters = [], array $sorts = []): LengthAwarePaginator
    {
        $query = Item::search();

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
        $fields = [
            'access_rights',
            'accrual_method',
            'author.person_id',
            'collection_method',
            'country.id',
            'district.id',
            'institution.id',
            'keyword.id',
            'language',
            'license',
            'location.id',
            'municipality_part.id',
            'municipality.id',
            'originator.person_id',
            'production_methods',
            'project.id',
            'region.id',
            'research_collection.id',
            'researcher.person_id',
            'type',
        ];

        $aggs = collect($fields)
            ->mapWithKeys(fn (string $field) => [
                $field => collect($filters)
                    ->except($field)
                    ->map(fn (array $values, string $key) => [
                        'terms' => [$key => $values],
                    ])
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
        $modelMapping = [
            'author.person_id' => Person::class,
            'country.id' => Country::class,
            'district.id' => District::class,
            'institution.id' => Organization::class,
            'keyword.id' => Keyword::class,
            'location.id' => Location::class,
            'municipality_part.id' => MunicipalityPart::class,
            'municipality.id' => Municipality::class,
            'originator.person_id' => Person::class,
            'project.id' => Project::class,
            'region.id' => Region::class,
            'research_collection.id' => ResearchCollection::class,
            'researcher.person_id' => Person::class,
        ];

        $enumMapping = [
            'access_rights' => AccessRights::class,
            'accrual_method' => AccrualMethod::class,
            'collection_method' => CollectionMethod::class,
            'language' => Language::class,
            'license' => License::class,
            'production_methods' => ProductionMethod::class,
            'type' => ItemType::class,
        ];

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
                    'id',
                    'locality_id',
                    'locality_type',
                    'document_id',
                    'document_overrides',
                ])
                ->with(Item::documentRelations($with, fn (BelongsTo $belongsTo) => $belongsTo->select([
                    'id',
                    'locality_id',
                    'locality_type',
                ])))
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
}
