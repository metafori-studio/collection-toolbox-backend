<?php

namespace Metafori\Etno\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
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
use Metafori\Etno\Models\ItemLocality;

/**
 * @mixin ItemLocality
 */
class ItemLocalityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'locality' => $this->whenLoaded('locality', function (Locality $locality): CountryResource|RegionResource|DistrictResource|MunicipalityResource|MunicipalityPartResource|LocationResource {
                return match (true) {
                    $locality instanceof Country => new CountryResource($locality),
                    $locality instanceof Region => new RegionResource($locality),
                    $locality instanceof District => new DistrictResource($locality),
                    $locality instanceof Municipality => new MunicipalityResource($locality),
                    $locality instanceof MunicipalityPart => new MunicipalityPartResource($locality),
                    $locality instanceof Location => new LocationResource($locality),
                };
            }),
        ];
    }
}
