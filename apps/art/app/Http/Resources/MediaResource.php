<?php

namespace Metafori\Art\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;
use Illuminate\Support\Collection as SupportCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @mixin Media
 */
class MediaResource extends JsonApiResource
{
    /**
     * @return array<string, mixed>
     */
    public function toAttributes(Request $request): array
    {
        return [
            'name' => $this->name,
            'file_name' => $this->file_name,
            'human_readable_size' => $this->human_readable_size,
            'mime_type' => $this->mime_type,
            'url' => $this->getUrl(),
            'conversions' => $this->getConversions(),
        ];
    }

    protected function getUrl(): string
    {
        return $this->getTemporaryUrl();
    }

    /**
     * @return SupportCollection<string, string>
     */
    protected function getConversions(): SupportCollection
    {
        return $this->getGeneratedConversions()
            ->mapWithKeys(fn (bool $hasGeneratedConversion, string $conversionName) => [
                $conversionName => $this->getTemporaryUrl(conversionName: $conversionName),
            ]);
    }

    /**
     * @return SupportCollection<string, bool>
     */
    protected function getGeneratedConversions(): SupportCollection
    {
        return $this->resource->getGeneratedConversions()->filter();
    }
}
