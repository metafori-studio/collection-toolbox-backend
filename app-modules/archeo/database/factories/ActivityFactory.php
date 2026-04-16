<?php

namespace Metafori\Archeo\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Archeo\Models\Activity;

class ActivityFactory extends Factory
{
    protected $model = Activity::class;

    public function definition(): array
    {
        return [
            'activity_number' => $this->faker->unique()->numerify('#####'),
            'activity_year_start' => $this->faker->year(),
            'activity_year_end' => $this->faker->year(),
            'activity_type' => $this->faker->word(),
            'research_leader' => $this->faker->name(),
            'size_category' => $this->faker->word(),
            'has_gis_link' => false,
            // Defaults to simulate having no coordinates unless overridden
            'latitude' => null,
            'longitude' => null,
        ];
    }
}
