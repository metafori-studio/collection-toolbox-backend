<?php

namespace Metafori\Core\Database\Factories\Concerns;

trait HasCoordinates
{
    public function withCoordinates(): static
    {
        return $this->state(fn () => [
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ]);
    }

    public function withoutCoordinates(): static
    {
        return $this->state(fn () => [
            'latitude' => null,
            'longitude' => null,
        ]);
    }
}
