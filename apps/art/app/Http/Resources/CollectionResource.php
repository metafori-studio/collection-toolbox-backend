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
     * @var array<string, class-string<JsonApiResource>>
     */
    public $relationships = [
        'category' => CollectionCategoryResource::class,
    ];

    /**
     * Transform the resource into an array of attributes.
     *
     * @return array<string, mixed>
     */
    public function toAttributes(Request $request): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'summary' => $this->summary,
            'author' => $this->author,
            'datespan' => $this->when($this->hasDatespan(), fn () => $this->datespan),
            'published_at' => $this->published_at,
            'is_featured' => $this->is_featured,
            'images' => $this->whenLoaded('previewArtworks', fn () => $this->previewArtworks
                ->map(fn ($artwork) => $artwork->media->first()?->getTemporaryUrl())
                ->filter()
                ->values()),
            'artworks_count' => $this->whenCounted('artworks'),
        ];
    }
}
