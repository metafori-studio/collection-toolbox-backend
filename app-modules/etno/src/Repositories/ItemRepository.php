<?php

namespace Metafori\Etno\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Cache;
use Metafori\Etno\Models\Item;
use OpenSearch\Client;

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
            if (\is_array($value) && ! \array_is_list($value)) {
                foreach ($value as $subKey => $subValue) {
                    $query->whereIn("{$field}.{$subKey}", (array) $subValue);
                }
            } elseif (\is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        foreach ($sorts as $sort) {
            $dir = str_starts_with($sort, '-') ? 'desc' : 'asc';
            $field = ltrim($sort, '-');
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

    public function mapPoints(): Collection
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
