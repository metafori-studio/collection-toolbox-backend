<?php

namespace Metafori\Etno\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Metafori\Etno\Models\PrecisionDate;

/**
 * @mixin PrecisionDate
 */
class PrecisionDateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (! $this->resource) {
            return [];
        }

        return [
            'precision' => $this->whenNotNull($this->precision?->value),
            'is_range' => $this->when($this->precision !== null, $this->is_range),
            'start' => $this->start?->toJSON(),
            'end' => $this->end?->toJSON(),
        ];
    }
}
