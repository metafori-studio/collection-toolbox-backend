<?php

namespace Metafori\Etno\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Metafori\Core\Enums\DatePrecision;

class PrecisionDate implements Arrayable
{
    public function __construct(
        public ?Carbon $start = null,
        public ?Carbon $end = null,
        public bool $is_range = false,
        public ?DatePrecision $precision = null,
    ) {}

    public function toArray(): array
    {
        return [
            'precision' => $this->precision?->value,
            'is_range' => $this->is_range,
            'start' => $this->start?->toJSON(),
            'end' => $this->end?->toJSON(),
        ];
    }
}
