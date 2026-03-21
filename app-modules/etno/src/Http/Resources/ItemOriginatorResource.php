<?php

namespace Metafori\Etno\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Metafori\Core\Http\Resources\PersonResource;
use Metafori\Etno\Models\ItemOriginator;

/**
 * @mixin ItemOriginator
 */
class ItemOriginatorResource extends JsonResource
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
            /** @var string|null */
            'label' => $this->label,
            'person' => new PersonResource($this->whenLoaded('person')),
        ];
    }
}
