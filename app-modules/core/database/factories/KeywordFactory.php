<?php

namespace Metafori\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Core\Models\Keyword;

/**
 * @extends Factory<Keyword>
 */
class KeywordFactory extends Factory
{
    protected $model = Keyword::class;

    public function definition(): array
    {
        return [
            'name' => [
                'en' => fake()->word(),
            ],
        ];
    }
}
