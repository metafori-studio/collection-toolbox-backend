<?php

namespace Metafori\Art\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Art\Models\Collection;

/**
 * @extends Factory<Collection>
 */
class CollectionFactory extends Factory
{
    protected $model = Collection::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => ['en' => fake()->words(2, true)],
        ];
    }
}
