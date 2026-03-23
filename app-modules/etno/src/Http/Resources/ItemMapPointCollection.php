<?php

namespace Metafori\Etno\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Metafori\Core\Models\Contracts\Locality;

class ItemMapPointCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->collection
            ->filter(fn (ItemMapPointResource $item) => $item
                ->whenLoaded('locality', fn (Locality $locality) => $locality->hasCoordinates())
            )
            ->toArray();
    }
}
