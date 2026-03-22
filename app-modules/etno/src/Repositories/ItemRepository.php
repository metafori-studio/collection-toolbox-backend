<?php

namespace Metafori\Etno\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Cache;
use Metafori\Core\Models\District;
use Metafori\Core\Models\Location;
use Metafori\Core\Models\Municipality;
use Metafori\Core\Models\MunicipalityPart;
use Metafori\Core\Models\Region;
use Metafori\Etno\Models\Item;

class ItemRepository
{
    protected const MAP_POINTS_CACHE_KEY = 'etno.item.map-points';

    public function findOrFail(string $id): Item
    {
        $morphWith = [
            Region::class => ['country'],
            District::class => ['region.country'],
            Municipality::class => ['district.region.country'],
            MunicipalityPart::class => ['municipality.district.region.country'],
        ];

        $with = [
            'institution',
            'project',
            'authors',
            'researchers',
            'originators.person',
            'keywords',
            'researchCollections',
            'locality' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                ...$morphWith,
                Location::class => [
                    'parent' => fn (MorphTo $morphTo) => $morphTo->morphWith($morphWith),
                ],
            ]),
        ];

        return Item::query()
            ->with(self::withInherited($with))
            ->findOrFail($id);
    }

    public function paginate(): LengthAwarePaginator
    {
        return Item::query()
            ->with(self::withInherited([
                'authors',
                'researchers',
                'originators.person',
            ]))
            ->paginate();
    }

    public function mapPoints(): Collection
    {
        return Cache::rememberForever(
            self::MAP_POINTS_CACHE_KEY,
            fn () => Item::query()
                ->select([
                    'id',
                    'locality_id',
                    'locality_type',
                ])
                ->with(['locality' => function ($query) {
                    $query->select([
                        'id',
                        'latitude',
                        'longitude',
                    ]);
                }])
                ->whereHas(
                    'locality',
                    fn (Builder $query) => $query
                        ->whereNotNull('latitude')
                        ->whereNotNull('longitude')
                )
                ->get()
        );
    }

    public function invalidateMapPointsCache(): void
    {
        Cache::forget(self::MAP_POINTS_CACHE_KEY);
    }

    protected static function withInherited(array $with): array
    {
        return [
            ...$with,
            'document' => fn (BelongsTo $belongsTo) => $belongsTo->with($with),
        ];
    }
}
