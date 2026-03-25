<?php

namespace Metafori\Etno\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Etno\Models\Document;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Metafori\Etno\Models\Document>
 */
class DocumentFactory extends Factory
{
    use Concerns\HasDocumentAttributes;
    use Concerns\HasLocality;

    protected $model = Document::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->unique()->regexify('[A-Z]{2}[0-9]{6}'),
            ...$this->documentAttributes(),
        ];
    }
}
