<?php

namespace Metafori\Art\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\JsonApi\JsonApiResource;
use Metafori\Art\Models\Artwork;
use Metafori\Core\Http\Resources\FuzzyDateResource;

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
            'creation_date' => FuzzyDateResource::make($this->creation_date),
            'work_types' => $this->work_types?->map(fn ($type): string => $type->getLabel())->values()->all() ?? [],
            'object_types' => $this->object_types?->map(fn ($type): string => $type->getLabel())->values()->all() ?? [],
            'mediums' => $this->mediums?->map(fn ($medium): string => $medium->getLabel())->values()->all() ?? [],
            'techniques' => $this->techniques?->map(fn ($technique): string => $technique->getLabel())->values()->all() ?? [],
            'acquisition_method' => $this->acquisition_method?->getLabel(),
            'acquisition_date' => FuzzyDateResource::make($this->acquisition_date),
            'measurements' => $this->measurements?->values()->all() ?? [],
        ];
    }
}
