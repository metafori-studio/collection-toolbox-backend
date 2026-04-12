<?php

namespace Metafori\Etno\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Metafori\Etno\Enums\AccessRights;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\Item;

/**
 * @extends Factory<Item>
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
            ...$this->documentAttributes(),
            'document_id' => Document::factory(),
            'suffix' => fake()->unique()->regexify('[A-Z0-9]{3}'),
        ];
    }

    public function withDocumentOverrides(): static
    {
        return $this->state([
            'document_overrides' => Item::INHERITABLES,
        ]);
    }

    public function published(bool $published = true): self
    {
        return $this->state(function (array $attributes) use ($published) {
            $attributes['document_overrides'][] = 'access_rights';
            $attributes['document_overrides'] = array_unique($attributes['document_overrides']);

            $accessRights = fake()->randomElement(
                $published
                ? AccessRights::published()
                : [AccessRights::ClosedAccess, null]
            );

            return [
                'access_rights' => $accessRights,
                'document_overrides' => $attributes['document_overrides'],
            ];
        });
    }

    public function withMedia(string $filename = 'document.pdf', array $customProperties = [], string $collection = 'documents', string $content = '%PDF-1.4'): static
    {
        return $this->afterCreating(function (Item $item) use ($filename, $customProperties, $collection, $content) {
            $item->addMedia(UploadedFile::fake()->createWithContent($filename, $content))
                ->withCustomProperties($customProperties)
                ->toMediaCollection($collection);
        });
    }

    public function withTranscribedMedia(
        string $txt = 'text',
        string $xml = '<?xml version="1.0" encoding="UTF-8"?>',
        string $filename = 'document.pdf',
        string $collection = 'documents'
    ): static {
        return $this->withMedia($filename, ['transcripts' => ['txt' => $txt, 'xml' => $xml]], $collection);
    }
}
