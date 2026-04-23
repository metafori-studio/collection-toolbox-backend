<?php

namespace Metafori\Core\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Metafori\Core\Models\MunicipalityPart;

/**
 * @mixin MunicipalityPart
 */
class MunicipalityPartResource extends JsonResource
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
            /** @var string */
            'name' => $this->name,
            'municipality' => new MunicipalityResource($this->whenLoaded('municipality')),
        ];
    }
}
