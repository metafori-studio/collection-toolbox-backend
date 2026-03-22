<?php

namespace Metafori\Archeo\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GalleryResource extends JsonResource
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
