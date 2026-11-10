<?php

namespace Metafori\Art\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;
use Metafori\Art\Models\Artwork;
use Metafori\Core\Http\Resources\EnumResource;

/**
 * @mixin Artwork
 */
class ArtworkResource extends JsonApiResource
{
    /**
     * @var array<string, class-string<JsonApiResource>>
     */
    public $relationships = [
        'authors' => ArtworkAuthorResource::class,
        'collections' => CollectionResource::class,
        'media' => MediaResource::class,
    ];

    /**
     * Transform the resource into an array of attributes.
     *
     * @return array<string, mixed>
     */
    public function toAttributes(Request $request): array
    {
        return [
            'identifier' => $this->identifier,
            'slug' => $this->slug,
            'title' => $this->title,
            'description' => $this->description,
            'dating' => $this->dating,
            'creation_date' => $this->creation_date,
            'work_types' => EnumResource::collection($this->work_types ?? []),
            'object_types' => EnumResource::collection($this->object_types ?? []),
            'mediums' => EnumResource::collection($this->mediums ?? []),
            'techniques' => EnumResource::collection($this->techniques ?? []),
            'acquisition_method' => EnumResource::make($this->acquisition_method),
            'acquisition_date' => $this->acquisition_date,
            'measurements' => MeasurementResource::collection($this->measurements ?? []),
        ];
    }
}
