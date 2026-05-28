<?php

namespace Metafori\Archeo\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Metafori\Archeo\Support\WatermarkedPdfPath;
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
     *     url: string|null,
     *     thumb: string|null,
     *     watermarked_url: string|null,
     *     size: string,
     *     mime_type: string
     * }
     */
    public function toArray(Request $request): array
    {
        $isPdf = $this->mime_type === 'application/pdf';
        $hasWatermark = $this->hasGeneratedConversion('watermarked');

        return [
            'name' => $this->file_name,
            'url' => (! $isPdf || ! $hasWatermark) ? $this->getUrl() : null,
            'thumb' => $this->hasGeneratedConversion('thumb') ? $this->getUrl('thumb') : null,
            'watermarked_url' => $hasWatermark ? $this->watermarkedUrl() : null,
            'size' => $this->human_readable_size,
            'mime_type' => $this->mime_type,
        ];
    }

    private function watermarkedUrl(): ?string
    {
        $path = WatermarkedPdfPath::forMedia($this->resource);

        if (! Storage::disk($this->disk)->exists($path)) {
            return null;
        }

        $disk = Storage::disk($this->disk);

        try {
            return $disk->temporaryUrl($path, now()->addMinutes(5));
        } catch (\RuntimeException) {
            return $disk->url($path);
        }
    }
}
