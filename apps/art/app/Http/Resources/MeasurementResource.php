<?php

namespace Metafori\Art\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Metafori\Art\Data\Measurement;
use Metafori\Core\Http\Resources\EnumResource;

/**
 * @mixin Measurement
 */
class MeasurementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'type' => EnumResource::make($this->type),
            'value' => $this->value,
            'unit' => EnumResource::make($this->unit),
        ];
    }
}
