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
        $expires = now()->addMinutes(20);

        return [
            'id' => $this->id,
            'title' => $this->title,
            'images' => $this->getMedia('gallery_images')->map(function ($media) use ($expires) {
                $isS3 = $media->disk === 's3';

                return [
                    'name' => $media->file_name,
                    'url' => $isS3 ? $media->getTemporaryUrl($expires) : $media->getUrl(),
                    'thumb' => $isS3 ? $media->getTemporaryUrl($expires, 'thumb') : $media->getUrl('thumb'),
                    'size' => $media->human_readable_size,
                    'mime_type' => $media->mime_type,
                ];
            }),
        ];
    }
}
