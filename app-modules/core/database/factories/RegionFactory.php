<?php

namespace Metafori\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Core\Models\Country;
use Metafori\Core\Models\Region;

/**
 * @extends Factory<Region>
 */
class RegionFactory extends Factory
{
    protected $model = Region::class;

    public function definition(): array
    {
        return [
            'country_id' => Country::factory(),
            'name' => [
                'en' => fake()->state(),
            ],
        ];
    }
}
