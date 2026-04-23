<?php

namespace Metafori\Etno\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Core\Models\Person;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\DocumentOriginator;

/**
 * @extends Factory<DocumentOriginator>
 */
class DocumentOriginatorFactory extends Factory
{
    protected $model = DocumentOriginator::class;

    public function definition(): array
    {
        return [
            'document_id' => Document::factory(),
            'person_id' => Person::factory(),
            'label' => [
                'en' => fake()->jobTitle(),
            ],
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }
}
