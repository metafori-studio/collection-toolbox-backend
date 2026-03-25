<?php

namespace Metafori\Etno\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\Item;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Metafori\Etno\Models\Item>
 */
class ItemFactory extends Factory
{
    use Concerns\HasDocumentAttributes;
    use Concerns\HasLocality;

    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->unique()->regexify('[A-Z]{2}[0-9]{6}:[a-z]{3}'),
            'document_id' => Document::factory(),
            ...$this->documentAttributes(),
        ];
    }

    public function withDocumentOverrides(): static
    {
        return $this->state([
            'document_overrides' => Item::INHERITABLES,
        ]);
    }
}
