<?php

namespace Metafori\Etno\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Etno\Models\ResearchCollection;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Metafori\Etno\Models\ResearchCollection>
 */
class ResearchCollectionFactory extends Factory
{
    protected $model = ResearchCollection::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => [
                'en' => fake()->sentence(),
            ],
        ];
    }
}
