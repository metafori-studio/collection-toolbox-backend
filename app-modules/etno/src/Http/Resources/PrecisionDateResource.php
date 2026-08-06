<?php

namespace Metafori\Etno\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Metafori\Core\Enums\DatePrecision;
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
        return [
            /** @var DatePrecision|null */
            'precision' => $this->resource?->precision?->value,
            /** @var bool */
            'is_range' => $this->resource?->is_range,
            /** @var Carbon|null */
            'start' => $this->resource?->start,
            /** @var Carbon|null */
            'end' => $this->resource?->end,
        ];
    }
}
