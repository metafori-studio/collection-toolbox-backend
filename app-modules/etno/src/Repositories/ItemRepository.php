<?php

namespace Metafori\Etno\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Metafori\Core\Models\District;
use Metafori\Core\Models\Location;
use Metafori\Core\Models\Municipality;
use Metafori\Core\Models\MunicipalityPart;
use Metafori\Core\Models\Region;
use Metafori\Etno\Models\Item;

class ItemRepository
{
    public function findOrFail(string $id): Item
    {
        $morphWith = [
            Region::class => ['country'],
            District::class => ['region.country'],
            Municipality::class => ['district.region.country'],
            MunicipalityPart::class => ['municipality.district.region.country'],
        ];

        return Item::query()
            ->with([
                'institution',
                'project',
                'authors',
                'researchers',
                'originators.person',
                'keywords',
                'researchCollections',
                'localities.locality' => fn (MorphTo $morphTo) => $morphTo->morphWith($morphWith + [
                    Location::class => [
                        'parent' => fn (MorphTo $morphTo) => $morphTo->morphWith($morphWith),
                    ],
                ]),
            ])
            ->findOrFail($id);
    }

    public function paginate(): LengthAwarePaginator
    {
        return Item::query()
            ->with([
                'authors',
                'researchers',
                'originators.person',
            ])
            ->paginate();
    }
}
