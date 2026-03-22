<?php

namespace Metafori\Etno\Database\Factories\Concerns;

trait HasLocality
{
    public function withoutLocality(): static
    {
        return $this->state([
            'locality_type' => null,
            'locality_id' => null,
        ]);
    }
}
