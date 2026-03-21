<?php

namespace Metafori\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Core\Database\Factories\Concerns\HasCoordinates;
use Metafori\Core\Models\District;
use Metafori\Core\Models\Municipality;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Metafori\Core\Models\Municipality>
 */
class MunicipalityFactory extends Factory
{
    use HasCoordinates;

    protected $model = Municipality::class;

    public function definition(): array
    {
        return [
            'district_id' => District::factory(),
            'name' => [
                'en' => fake()->city(),
            ],
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];
    }
}
