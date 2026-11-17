<?php

namespace Metafori\Art\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Art\Models\Collection;
use Metafori\Art\Models\CollectionCategory;

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
            'collection_category_id' => CollectionCategory::factory(),
            'title' => [
                'cs' => fake()->words(2, true),
                'en' => fake()->words(2, true),
            ],
            'description' => [
                'cs' => fake()->paragraph(),
                'en' => fake()->paragraph(),
            ],
            'summary' => [
                'cs' => fake()->sentence(),
                'en' => fake()->sentence(),
            ],
            'author' => [
                'cs' => fake()->name(),
                'en' => fake()->name(),
            ],
            'published_at' => now(),
            'is_featured' => false,
        ];
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_featured' => true,
        ]);
    }

    public function unpublished(): static
    {
        return $this->state(fn (array $attributes): array => [
            'published_at' => null,
        ]);
    }
}
