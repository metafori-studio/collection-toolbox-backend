<?php

namespace Metafori\Art\Data;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Metafori\Art\Enums\MeasurementType;
use Metafori\Art\Enums\MeasurementUnit;

class Measurement implements Arrayable, JsonSerializable
{
    public ?MeasurementType $type;

    public ?float $value;

    public ?MeasurementUnit $unit;

    /**
     * @param  array{type?: MeasurementType|string|null, value?: float|null, unit?: MeasurementUnit|string|null}  $data
     */
    public function __construct(array $data = [])
    {
        $this->type = \is_string($data['type'] ?? null)
            ? MeasurementType::tryFrom($data['type'])
            : ($data['type'] ?? null);

        $this->value = $data['value'] ?? null;

        $this->unit = \is_string($data['unit'] ?? null)
            ? MeasurementUnit::tryFrom($data['unit'])
            : ($data['unit'] ?? null);
    }

    /**
     * @return array{type: string|null, value: float|null, unit: string|null}
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type?->value,
            'value' => $this->value,
            'unit' => $this->unit?->value,
        ];
    }

    /**
     * @return array{type: string|null, value: float|null, unit: string|null}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
