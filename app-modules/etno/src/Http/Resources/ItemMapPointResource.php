<?php

namespace Metafori\Etno\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Metafori\Etno\Http\Resources\Concerns\InheritsDocument;
use Metafori\Etno\Models\Item;

/**
 * @mixin Item
 */
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
            'id' => $this->identifier,
            'latitude' => $this->locality?->latitude,
            'longitude' => $this->locality?->longitude,
        ];
    }
}
