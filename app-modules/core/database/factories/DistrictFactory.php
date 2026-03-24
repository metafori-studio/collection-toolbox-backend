<?php

namespace Metafori\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Core\Database\Factories\Concerns\HasCoordinates;
use Metafori\Core\Models\District;
use Metafori\Core\Models\Region;

/**
 * @extends Factory<District>
 */
class DistrictFactory extends Factory
{
    use HasCoordinates;

    protected $model = District::class;

    public function definition(): array
    {
        return [
            'region_id' => Region::factory(),
            'name' => [
                'en' => fake()->city(),
            ],
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];
    }
}
