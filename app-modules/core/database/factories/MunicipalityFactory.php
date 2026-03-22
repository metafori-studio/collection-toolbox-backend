<?php

namespace Metafori\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Core\Models\District;
use Metafori\Core\Models\Municipality;

/**
 * @extends Factory<Municipality>
 */
class MunicipalityFactory extends Factory
{
    protected $model = Municipality::class;

    public function definition(): array
    {
        return [
            'district_id' => District::factory(),
            'name' => [
                'en' => fake()->city(),
            ],
        ];
    }
}
