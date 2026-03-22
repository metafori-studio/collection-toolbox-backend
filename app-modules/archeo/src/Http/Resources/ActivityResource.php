<?php

namespace Metafori\Archeo\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
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
            'wgs84_coordinate_x' => $this->wgs84_coordinate_x,
            'wgs84_coordinate_y' => $this->wgs84_coordinate_y,
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
            'file_name' => $this->file_name,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'galleries' => GalleryResource::collection($this->whenLoaded('galleries')),
        ];
    }
}
