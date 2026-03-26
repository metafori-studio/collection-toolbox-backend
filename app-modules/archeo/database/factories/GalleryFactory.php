<?php

namespace Metafori\Archeo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Archeo\Models\Activity;
use Metafori\Archeo\Models\Gallery;

/**
 * @extends Factory<Gallery>
 */
class GalleryFactory extends Factory
{
    protected $model = Gallery::class;

    public function definition(): array
    {
        return [
            'activity_id' => Activity::factory(),
            'title' => fake()->sentence(3),
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}