<?php

namespace Metafori\Etno\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;
use Metafori\Core\Http\Resources\KeywordResource;
use Metafori\Core\Http\Resources\OrganizationResource;
use Metafori\Core\Http\Resources\PersonResource;
use Metafori\Etno\Enums\ExtentUnit;
use Metafori\Etno\Enums\ProductionMethod;
use Metafori\Etno\Http\Resources\Concerns\ResolvesLocality;
use Metafori\Etno\Models\Document;

/**
 * @mixin Document
 */
class DocumentResource extends JsonResource
{
    use ResolvesLocality;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /** @var string */
            'id' => $this->id,
            /** @var string|null */
            'doi' => $this->doi,
            /** @var string|null */
            'title' => $this->title,
            /** @var string|null */
            'subtitle' => $this->subtitle,
            /** @var string|null */
            'abstract' => $this->abstract,
            /** @var string|null */
            'general_note' => $this->general_note,
            /** @var string|null */
            'terms_of_use' => $this->terms_of_use,
            /** @var string|null */
            'location_note' => $this->location_note,
            /** @var string|null */
            'content_note' => $this->content_note,
            /** @var string|null */
            'technical_note' => $this->technical_note,
            'type' => $this->type,
            /** @var array{value: string, unit: ExtentUnit}[] */
            'extents' => $this->extents,
            'languages' => $this->languages,
            'accrual_method' => $this->accrual_method,
            'collection_method' => $this->collection_method,
            'access_rights' => $this->access_rights,
            'license' => $this->license,
            /** @var ProductionMethod[] */
            'production_methods' => $this->production_methods,
            'time_period' => [
                'precision' => $this->time_period_settings['precision'] ?? new MissingValue,
                'is_range' => $this->time_period_settings['is_range'] ?? new MissingValue,
                'start' => $this->time_period_start,
                'end' => $this->time_period_end,
            ],
            'submission_date' => [
                'precision' => $this->submission_date_settings['precision'] ?? new MissingValue,
                'is_range' => $this->submission_date_settings['is_range'] ?? new MissingValue,
                'start' => $this->submission_date_start,
                'end' => $this->submission_date_end,
            ],
            'publication_date' => [
                'precision' => $this->publication_date_settings['precision'] ?? new MissingValue,
                'is_range' => $this->publication_date_settings['is_range'] ?? new MissingValue,
                'start' => $this->publication_date_start,
                'end' => $this->publication_date_end,
            ],
            'institution' => new OrganizationResource($this->whenLoaded('institution')),
            'project' => new ProjectResource($this->whenLoaded('project')),
            'locality' => $this->whenLoaded('locality', $this->resolveLocality(...)),
            'authors' => PersonResource::collection($this->whenLoaded('authors')),
            'researchers' => PersonResource::collection($this->whenLoaded('researchers')),
            'originators' => OriginatorResource::collection($this->whenLoaded('originators')),
            'keywords' => KeywordResource::collection($this->whenLoaded('keywords')),
            'research_collections' => ResearchCollectionResource::collection($this->whenLoaded('researchCollections')),
            /** @var string|null */
            'how_to_cite' => $this->how_to_cite,
        ];
    }
}
