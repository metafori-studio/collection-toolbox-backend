<?php

namespace Metafori\Etno\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Etno\Models\Document;

/**
 * @extends Factory<Document>
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
        return $this->documentAttributes();
    }
}
