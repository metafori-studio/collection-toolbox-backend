<?php

namespace Metafori\Art\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Art\Models\CollectionCategory;

/**
 * @extends Factory<CollectionCategory>
 */
class CollectionCategoryFactory extends Factory
{
    protected $model = CollectionCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => [
                'cs' => fake()->words(2, true),
                'en' => fake()->words(2, true),
            ],
        ];
    }
}
