<?php

namespace Metafori\Etno\Models;

use Illuminate\Contracts\Support\Arrayable;
use Metafori\Etno\Enums\ExtentUnit;

class Extent implements Arrayable
{
    public ?string $value;

    public ?ExtentUnit $unit;

    public function __construct(?string $value = null, ExtentUnit|string|null $unit = null)
    {
        $this->value = $value;
        $this->unit = $unit instanceof ExtentUnit ? $unit : ExtentUnit::tryFrom($unit);
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'unit' => $this->unit,
        ];
    }
}
