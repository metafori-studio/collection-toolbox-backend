<?php

namespace Metafori\Etno\Models;

use Illuminate\Contracts\Support\Arrayable;
use Metafori\Etno\Enums\ExtentUnit;

class Extent implements Arrayable
{
    public ?string $value;

    public ?ExtentUnit $unit;

    public function __construct(?string $value = null, ?string $unit = null)
    {
        $this->value = $value;
        $this->unit = ExtentUnit::tryFrom($unit);
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'unit' => $this->unit,
        ];
    }
}
