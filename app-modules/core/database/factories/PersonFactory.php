<?php

namespace Metafori\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Core\Models\Person;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Metafori\Core\Models\Person>
 */
class PersonFactory extends Factory
{
    protected $model = Person::class;

    public function definition(): array
    {
        return [
            'given_name' => $this->faker->firstName(),
            'family_name' => $this->faker->lastName(),
            'orcid' => $this->faker->optional()->orcid(),
        ];
    }
}
