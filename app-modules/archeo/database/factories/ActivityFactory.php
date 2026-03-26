<?php

namespace Metafori\Archeo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Archeo\Models\Activity;

/**
 * @extends Factory<Activity>
 */
class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition(): array
    {
        return [
            'activity_number' => (string) fake()->unique()->numerify('######'),
            'activity_year_start' => fake()->numberBetween(1990, 2020),
            'activity_year_end' => fake()->numberBetween(2020, 2025),
            'activity_type' => fake()->randomElement(['Excavation', 'Survey', 'Monitoring']),
            'cvs_number' => fake()->numberBetween(1000, 9999),
            'research_leader' => fake()->name(),
            'size_category' => fake()->randomElement(['small', 'medium', 'large']),
            'author_ns' => [fake()->name()],
            'municipality' => fake()->city(),
            'has_gis_link' => false,
        ];
    }
}