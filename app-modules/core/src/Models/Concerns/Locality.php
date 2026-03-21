<?php

namespace Metafori\Core\Models\Concerns;

trait Locality
{
    protected function initializeLocality(): void
    {
        $this->mergeCasts([
            'latitude' => 'decimal:6',
            'longitude' => 'decimal:6',
        ]);
    }
}
