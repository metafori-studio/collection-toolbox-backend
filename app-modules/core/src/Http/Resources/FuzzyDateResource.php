<?php

namespace Metafori\Core\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Metafori\Core\Data\FuzzyDate;

/**
 * @mixin FuzzyDate
 */
class FuzzyDateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>|null
     */
    public function toArray(Request $request): ?array
    {
        if ($this->resource === null) {
            return null;
        }

        if (! $this->resource->isRange) {
            return [
                'is_range' => false,
                'date' => $this->resource->start?->toDateString(),
                'precision' => $this->resource->startPrecision?->value,
            ];
        }

        return [
            'is_range' => true,
            'start' => [
                'date' => $this->resource->start?->toDateString(),
                'precision' => $this->resource->startPrecision?->value,
            ],
            'end' => [
                'date' => $this->resource->end?->toDateString(),
                'precision' => $this->resource->endPrecision?->value,
            ],
        ];
    }
}
