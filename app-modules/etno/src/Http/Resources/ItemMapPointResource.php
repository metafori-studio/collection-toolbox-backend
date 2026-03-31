<?php

namespace Metafori\Etno\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Metafori\Etno\Http\Resources\Concerns\InheritsDocument;

class ItemMapPointResource extends JsonResource
{
    use InheritsDocument;

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
            /** @var float */
            'latitude' => $this->locality->latitude,
            /** @var float */
            'longitude' => $this->locality->longitude,
        ];
    }
}
