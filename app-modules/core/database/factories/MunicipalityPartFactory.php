<?php

namespace Metafori\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Core\Models\Municipality;
use Metafori\Core\Models\MunicipalityPart;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Metafori\Core\Models\MunicipalityPart>
 */
class MunicipalityPartFactory extends Factory
{
    protected $model = MunicipalityPart::class;

    public function definition(): array
    {
        return [
            'municipality_id' => Municipality::factory(),
            'name' => [
                'en' => fake()->streetName(),
            ],
        ];
    }
}
