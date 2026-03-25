<?php

namespace Metafori\Etno\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Etno\Models\Project;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Metafori\Etno\Models\Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => [
                'en' => fake()->sentence(),
            ],
        ];
    }
}
