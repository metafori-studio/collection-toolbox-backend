<?php

namespace Metafori\Core\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @mixin Media
 */
class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'file_name' => $this->file_name,
            'human_readable_size' => $this->human_readable_size,
            'mime_type' => $this->mime_type,
            /** @var string */
            'url' => $this->getTemporaryUrl(),
            /** @var array<string, string> */
            'conversions' => $this->getGeneratedConversions()
                ->filter()
                ->mapWithKeys(fn (true $hasGeneratedConversion, string $conversionName) => [
                    $conversionName => $this->getTemporaryUrl(conversionName: $conversionName),
                ]),
        ];
    }
}
