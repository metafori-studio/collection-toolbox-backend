<?php

namespace Metafori\Etno\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\MissingValue;
use Metafori\Core\Http\Resources\MediaResource as CoreMediaResource;
use Metafori\Etno\Enums\TranscriptFormat;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @mixin Media
 */
class MediaResource extends CoreMediaResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            /** @var string */
            'transcript' => $this->custom_properties['transcripts'][TranscriptFormat::Txt->value] ?? new MissingValue,
        ];
    }
}
