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

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null
            && $this->longitude !== null;
    }

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
