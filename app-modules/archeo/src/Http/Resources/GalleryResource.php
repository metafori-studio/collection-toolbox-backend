<?php

namespace Metafori\Archeo\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 */
class GalleryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{
     *     id: int,
     *     title: string,
     *     description: string|null,
     *     images: array<int, array{
     *         name: string,
     *         url: string,
     *         thumb: string,
     *         size: string,
     *         mime_type: string
     *     }>
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'images' => $this->getMedia('gallery_images')->map(fn ($media) => [
                'name' => $media->file_name,
                'url' => $media->getTemporaryUrl(now()->addMinutes(20)),
                'thumb' => $media->getTemporaryUrl(now()->addMinutes(20), 'thumb'),
                'size' => $media->human_readable_size,
                'mime_type' => $media->mime_type,
            ]),
        ];
    }
}
