<?php

namespace Metafori\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Core\Models\Organization;

/**
 * @extends Factory<Organization>
 */
class OrganizationFactory extends Factory
{
    protected $model = Organization::class;

    public function definition(): array
    {
        return [
            'name' => [
                'en' => fake()->company(),
            ],
            'ror_id' => $this->faker->optional()->rorId(),
        ];
    }
}
