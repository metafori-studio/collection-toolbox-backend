<?php

namespace Metafori\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Core\Database\Factories\Concerns\HasCoordinates;
use Metafori\Core\Models\Country;
use Metafori\Core\Models\District;
use Metafori\Core\Models\Location;
use Metafori\Core\Models\Municipality;
use Metafori\Core\Models\MunicipalityPart;
use Metafori\Core\Models\Region;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Metafori\Core\Models\Location>
 */
class LocationFactory extends Factory
{
    use HasCoordinates;

    protected $model = Location::class;

    public function definition(): array
    {
        $parentClass = fake()->randomElement([
            Country::class,
            Region::class,
            District::class,
            Municipality::class,
            MunicipalityPart::class,
        ]);

        return [
            'parent_id' => $parentClass::factory(),
            'parent_type' => (new $parentClass)->getMorphClass(),
            'name' => [
                'en' => fake()->city(),
            ],
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
        ];
    }
}
