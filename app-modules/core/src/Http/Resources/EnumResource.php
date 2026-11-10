<?php

namespace Metafori\Core\Http\Resources;

use BackedEnum;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin BackedEnum&HasLabel
 */
class EnumResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{value: int|string, label: string|null}
     */
    public function toArray(Request $request): array
    {
        return [
            'value' => $this->value,
            'label' => $this->getLabel(),
        ];
    }
}
