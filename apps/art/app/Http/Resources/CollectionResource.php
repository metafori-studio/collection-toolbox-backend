<?php

namespace Metafori\Art\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;
use Metafori\Art\Models\Collection;

/**
 * @mixin Collection
 */
class CollectionResource extends JsonApiResource
{
    /**
     * Transform the resource into an array of attributes.
     *
     * @return array<string, mixed>
     */
    public function toAttributes(Request $request): array
    {
        return [
            'title' => $this->title,
            'artworks_count' => $this->whenCounted('artworks'),
        ];
    }
}
