<?php

namespace Metafori\Etno\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Metafori\Core\Filament\Resources\CountryResource;
use Metafori\Core\Filament\Resources\DistrictResource;
use Metafori\Core\Filament\Resources\LocationResource;
use Metafori\Core\Filament\Resources\MunicipalityPartResource;
use Metafori\Core\Filament\Resources\MunicipalityResource;
use Metafori\Core\Http\Resources\KeywordResource;
use Metafori\Core\Http\Resources\OrganizationResource;
use Metafori\Core\Http\Resources\PersonResource;
use Metafori\Core\Http\Resources\RegionResource;
use Metafori\Core\Models\Contracts\Locality;
use Metafori\Core\Models\Country;
use Metafori\Core\Models\District;
use Metafori\Core\Models\Location;
use Metafori\Core\Models\Municipality;
use Metafori\Core\Models\MunicipalityPart;
use Metafori\Core\Models\Region;
use Metafori\Etno\Models\Item;

/**
 * @mixin Item
 */
class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /** @var string */
            'id' => $this->id,
            /** @var string|null */
            'doi' => $this->doi,
            /** @var string|null */
            'title' => $this->title,
            /** @var string|null */
            'subtitle' => $this->subtitle,
            /** @var string|null */
            'abstract' => $this->abstract,
            /** @var string|null */
            'general_note' => $this->general_note,
            /** @var string|null */
            'terms_of_use' => $this->terms_of_use,
            /** @var string|null */
            'location_note' => $this->location_note,
            /** @var string|null */
            'content_note' => $this->content_note,
            /** @var string|null */
            'technical_note' => $this->technical_note,
            'type' => $this->type,
            'extent' => $this->extent,
            'extent_unit' => $this->extent_unit,
            'language' => $this->language,
            'accrual_method' => $this->accrual_method,
            'collection_method' => $this->collection_method,
            'access_rights' => $this->access_rights,
            'license' => $this->license,
            /** @var \Metafori\Etno\Enums\ProductionMethod[] */
            'production_methods' => $this->production_methods,
            'time_period_start' => $this->time_period_start,
            'time_period_end' => $this->time_period_end,
            /** @var array{is_range: bool, precision: \Metafori\Core\Enums\DatePrecision}|null */
            'time_period_settings' => $this->time_period_settings,
            'submission_date_start' => $this->submission_date_start,
            'submission_date_end' => $this->submission_date_end,
            /** @var array{is_range: bool, precision: \Metafori\Core\Enums\DatePrecision}|null */
            'submission_date_settings' => $this->submission_date_settings,
            'publication_date_start' => $this->publication_date_start,
            'publication_date_end' => $this->publication_date_end,
            /** @var array{is_range: bool, precision: \Metafori\Core\Enums\DatePrecision}|null */
            'publication_date_settings' => $this->publication_date_settings,
            'institution' => new OrganizationResource($this->whenLoaded('institution')),
            'project' => new ProjectResource($this->whenLoaded('project')),
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
            'authors' => PersonResource::collection($this->whenLoaded('authors')),
            'researchers' => PersonResource::collection($this->whenLoaded('researchers')),
            'originators' => ItemOriginatorResource::collection($this->whenLoaded('originators')),
            'keywords' => KeywordResource::collection($this->whenLoaded('keywords')),
            'research_collections' => ResearchCollectionResource::collection($this->whenLoaded('researchCollections')),
        ];
    }
}
