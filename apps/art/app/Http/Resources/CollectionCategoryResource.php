<?php

namespace Metafori\Art\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;
use Metafori\Art\Models\CollectionCategory;

/**
 * @mixin CollectionCategory
 */
class CollectionCategoryResource extends JsonApiResource
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
        ];
    }
}

