<?php

namespace Metafori\Archeo\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property Media $resource
 */
class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{
     *     name: string,
     *     url: string,
     *     thumb: string,
     *     size: string,
     *     mime_type: string
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->file_name,
            'url' => $this->getUrl(),
            'thumb' => $this->hasGeneratedConversion('thumb') ? $this->getUrl('thumb') : null,
            'size' => $this->human_readable_size,
            'mime_type' => $this->mime_type,
        ];
    }
}
