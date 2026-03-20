<?php

namespace Metafori\Etno\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Metafori\Etno\Models\Item;

/**
 * @mixin Item
 */
class ItemMapPointResource extends JsonResource
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
            'latitude' => (float) $this->locality->latitude,
            'longitude' => (float) $this->locality->longitude,
        ];
    }
}
