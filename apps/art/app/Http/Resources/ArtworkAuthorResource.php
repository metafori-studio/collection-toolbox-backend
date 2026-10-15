<?php

namespace Metafori\Art\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;
use Metafori\Art\Models\ArtworkAuthor;

/**
 * @mixin ArtworkAuthor
 */
class ArtworkAuthorResource extends JsonApiResource
{
    /**
     * @var array<string, class-string<JsonApiResource>>
     */
    public $relationships = [
        'person' => PersonResource::class,
    ];

    /**
     * Transform the resource into an array of attributes.
     *
     * @return array<string, mixed>
     */
    public function toAttributes(Request $request): array
    {
        return [
            'label' => $this->label,
            'sort_order' => $this->sort_order,
        ];
    }
}
