<?php

namespace Metafori\Etno\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Core\Models\Person;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Models\ItemOriginator;

/**
 * @extends Factory<ItemOriginator>
 */
class ItemOriginatorFactory extends Factory
{
    protected $model = ItemOriginator::class;

    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'person_id' => Person::factory(),
            'label' => [
                'en' => fake()->jobTitle(),
            ],
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
