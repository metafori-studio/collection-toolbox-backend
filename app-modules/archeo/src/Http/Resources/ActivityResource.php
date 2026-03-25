<?php

namespace Metafori\Archeo\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $activity_number
 * @property int $activity_year_start
 * @property int $activity_year_end
 * @property string $activity_type
 * @property string|null $action_number
 * @property int|null $registration_year
 * @property string|null $cadastral_area
 * @property string|null $municipality
 * @property string|null $position
 * @property string|null $district
 * @property int|null $localization_degree
 * @property float|null $coordinate_x
 * @property float|null $coordinate_y
 * @property array{latitude: float, longitude: float}|null $gcs_coordinates
 * @property bool $has_gis_link
 * @property int $cvs_number
 * @property string $research_leader
 * @property string[]|null $author_ns
 * @property string|null $institution
 * @property string[]|null $dating_ns
 * @property string[]|null $dating_ceans
 * @property string[]|null $dating_site_type
 * @property string|null $site_type_original
 * @property string $size_category
 * @property string|null $import_id
 */
class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{
     *     id: int,
     *     activity_number: string,
     *     activity_year_start: int,
     *     activity_year_end: int,
     *     activity_type: string,
     *     action_number: string|null,
     *     registration_year: int|null,
     *     cadastral_area: string|null,
     *     municipality: string|null,
     *     position: string|null,
     *     district: string|null,
     *     localization_degree: int|null,
     *     coordinate_x: float|null,
     *     coordinate_y: float|null,
     *     gcs_coordinates: array{latitude: float, longitude: float}|null,
     *     has_gis_link: bool,
     *     cvs_number: int,
     *     research_leader: string,
     *     author_ns: string[]|null,
     *     institution: string|null,
     *     dating_ns: string[]|null,
     *     dating_ceans: string[]|null,
     *     dating_site_type: string[]|null,
     *     site_type_original: string|null,
     *     size_category: string,
     *     import_id: string|null,
     *     created_at: string,
     *     updated_at: string,
     *     galleries: array<int, GalleryResource>
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'activity_number' => $this->activity_number,
            'activity_year_start' => $this->activity_year_start,
            'activity_year_end' => $this->activity_year_end,
            'activity_type' => $this->activity_type,
            'action_number' => $this->action_number,
            'registration_year' => $this->registration_year,
            'cadastral_area' => $this->cadastral_area,
            'municipality' => $this->municipality,
            'position' => $this->position,
            'district' => $this->district,
            'localization_degree' => $this->localization_degree,
            'coordinate_x' => $this->coordinate_x,
            'coordinate_y' => $this->coordinate_y,
            'gcs_coordinates' => $this->gcs_coordinates,
            'has_gis_link' => $this->has_gis_link,
            'cvs_number' => $this->cvs_number,
            'research_leader' => $this->research_leader,
            'author_ns' => $this->author_ns,
            'institution' => $this->institution,
            'dating_ns' => $this->dating_ns,
            'dating_ceans' => $this->dating_ceans,
            'dating_site_type' => $this->dating_site_type,
            'site_type_original' => $this->site_type_original,
            'size_category' => $this->size_category,
            'galleries' => GalleryResource::collection($this->whenLoaded('galleries')),
        ];
    }
}
