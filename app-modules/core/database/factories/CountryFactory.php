<?php

namespace Metafori\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Core\Database\Factories\Concerns\HasCoordinates;
use Metafori\Core\Models\Country;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Metafori\Core\Models\Country>
 */
class CountryFactory extends Factory
{
    use HasCoordinates;

    protected $model = Country::class;

    public function definition(): array
    {
        return [
            'name' => [
                'en' => fake()->country(),
            ],
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];
    }
}
