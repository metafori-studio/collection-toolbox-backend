<?php

namespace Metafori\Etno\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Core\Models\Country;
use Metafori\Core\Models\District;
use Metafori\Core\Models\Location;
use Metafori\Core\Models\Municipality;
use Metafori\Core\Models\MunicipalityPart;
use Metafori\Core\Models\Region;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Models\ItemLocality;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Metafori\Etno\Models\ItemLocality>
 */
class ItemLocalityFactory extends Factory
{
    protected $model = ItemLocality::class;

    public function definition(): array
    {
        $localityClass = fake()->randomElement([
            Country::class,
            Region::class,
            District::class,
            Municipality::class,
            MunicipalityPart::class,
            Location::class,
        ]);

        return [
            'item_id' => Item::factory(),
            'locality_type' => (new $localityClass)->getMorphClass(),
            'locality_id' => $localityClass::factory(),
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
