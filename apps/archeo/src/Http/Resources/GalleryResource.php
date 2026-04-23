<?php

namespace Metafori\Archeo\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $title
 */
class GalleryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{
     *     id: int,
     *     title: string,
     *     images: AnonymousResourceCollection
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'images' => MediaResource::collection($this->getMedia('gallery_images')),
        ];
    }
}
