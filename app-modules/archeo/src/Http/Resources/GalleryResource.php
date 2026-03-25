<?php

namespace Metafori\Archeo\Http\Resources;

use Illuminate\Http\Request;
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
            'images' => $this->getMedia('gallery_images')->map(function ($media) {

                return [
                    'name' => $media->file_name,
                    'url' => $media->getUrl(),
                    'thumb' => $media->getUrl('thumb'),
                    'size' => $media->human_readable_size,
                    'mime_type' => $media->mime_type,
                ];
            }),
        ];
    }
}
