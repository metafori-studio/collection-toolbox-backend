<?php

namespace Metafori\Core\Models\Contracts;

/**
 * @property-read float|null $latitude
 * @property-read float|null $longitude
 */
interface Locality
{
    public function hasCoordinates(): bool;
}
