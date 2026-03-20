<?php

namespace Metafori\Core\Models\Concerns;

trait Locality
{
    public function casts(): array
    {
        return [
            'latitude' => 'decimal:6',
            'longitude' => 'decimal:6',
        ];
    }
}
