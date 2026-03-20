<?php

namespace Metafori\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Core\Models\Country;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Metafori\Core\Models\Country>
 */
class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition(): array
    {
        return [
            'name' => [
                'en' => fake()->country(),
            ],
        ];
    }
}
