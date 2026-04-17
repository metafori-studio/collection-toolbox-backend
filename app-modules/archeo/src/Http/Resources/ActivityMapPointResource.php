<?php

namespace Metafori\Archeo\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Metafori\Archeo\Models\Activity;

/**
 * @mixin Activity
 */
class ActivityMapPointResource extends JsonResource
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
            'id' => $this->activity_number,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'localization_degree' => $this->localization_degree,
        ];
    }
}
