<?php

namespace Metafori\Archeo\Http\Resources;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Metafori\Archeo\Support\WatermarkedPdfPath;
use Metafori\Core\Http\Resources\MediaResource as CoreMediaResource;
use RuntimeException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @mixin Media
 */
class MediaResource extends CoreMediaResource
{
    protected function getUrl(): string
    {
        if ($this->mime_type === 'application/pdf' && $this->hasGeneratedConversion('watermarked')) {
            return $this->watermarkedUrl();
        }

        return parent::getUrl();
    }

    /**
     * @return Collection<string, bool>
     */
    protected function getGeneratedConversions(): Collection
    {
        return parent::getGeneratedConversions()
            ->except(['watermarked']);
    }

    private function watermarkedUrl(): string
    {
        $path = WatermarkedPdfPath::forMedia($this->resource);
        $disk = Storage::disk($this->disk);

        try {
            return $disk->temporaryUrl($path, \now()->addMinutes(5));
        } catch (RuntimeException) {
            return $disk->url($path);
        }
    }
}
