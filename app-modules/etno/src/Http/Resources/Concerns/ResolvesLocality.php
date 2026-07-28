<?php

namespace Metafori\Etno\Http\Resources\Concerns;

use Metafori\Core\Http\Resources\CountryResource;
use Metafori\Core\Http\Resources\DistrictResource;
use Metafori\Core\Http\Resources\LocationResource;
use Metafori\Core\Http\Resources\MunicipalityPartResource;
use Metafori\Core\Http\Resources\MunicipalityResource;
use Metafori\Core\Http\Resources\RegionResource;
use Metafori\Core\Models\Contracts\Locality;
use Metafori\Core\Models\Country;
use Metafori\Core\Models\District;
use Metafori\Core\Models\Location;
use Metafori\Core\Models\Municipality;
use Metafori\Core\Models\MunicipalityPart;
use Metafori\Core\Models\Region;

trait ResolvesLocality
{
    /**
     * @return array<int, CountryResource|RegionResource|DistrictResource|MunicipalityResource|MunicipalityPartResource|LocationResource>
     */
    protected function resolveLocality(Locality $locality): array
    {
        $localities = [];
        $current = $locality;

        while ($current) {
            $localities[] = match (true) {
                $current instanceof Country => new CountryResource($current),
                $current instanceof Region => new RegionResource($current),
                $current instanceof District => new DistrictResource($current),
                $current instanceof Municipality => new MunicipalityResource($current),
                $current instanceof MunicipalityPart => new MunicipalityPartResource($current),
                $current instanceof Location => new LocationResource($current),
            };

            $current = match (true) {
                $current instanceof MunicipalityPart => $current->relationLoaded('municipality') ? $current->municipality : null,
                $current instanceof Municipality => $current->relationLoaded('district') ? $current->district : null,
                $current instanceof District => $current->relationLoaded('region') ? $current->region : null,
                $current instanceof Region => $current->relationLoaded('country') ? $current->country : null,
                $current instanceof Location => $current->relationLoaded('parent') ? $current->parent : null,
                default => null,
            };
        }

        return $localities;
    }
}
