<?php

namespace Metafori\Etno\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Metafori\Etno\Enums\AccessRights;
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
        return [
            'id' => fake()->unique()->regexify('[A-Z]{2}[0-9]{5,6}'),
            ...$this->documentAttributes(),
        ];
    }

    public function published(bool $published = true): self
    {
        $accessRights = fake()->randomElement(
            $published
            ? AccessRights::published()
            : [AccessRights::ClosedAccess, null]
        );

        return $this->state([
            'access_rights' => $accessRights,
        ]);
    }
}
