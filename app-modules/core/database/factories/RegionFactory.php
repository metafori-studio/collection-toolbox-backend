<?php

namespace Metafori\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Core\Database\Factories\Concerns\HasCoordinates;
use Metafori\Core\Models\Country;
use Metafori\Core\Models\Region;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Metafori\Core\Models\Region>
 */
class RegionFactory extends Factory
{
    use HasCoordinates;

    protected $model = Region::class;

    public function definition(): array
    {
        return [
            'country_id' => Country::factory(),
            'name' => [
                'en' => fake()->state(),
            ],
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];
    }
}
