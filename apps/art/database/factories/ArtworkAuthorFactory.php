<?php

namespace Metafori\Art\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Art\Models\Artwork;
use Metafori\Art\Models\ArtworkAuthor;

/**
 * @extends Factory<ArtworkAuthor>
 */
class ArtworkAuthorFactory extends Factory
{
    protected $model = ArtworkAuthor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'artwork_id' => Artwork::factory(),
            'label' => fake()->name(),
            'sort_order' => 0,
        ];
    }
}
