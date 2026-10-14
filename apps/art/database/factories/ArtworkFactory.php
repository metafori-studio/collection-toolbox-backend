<?php

namespace Metafori\Art\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Metafori\Art\Enums\AcquisitionMethod;
use Metafori\Art\Models\Artwork;
use Metafori\Core\Data\FuzzyDate;
use Metafori\Core\Enums\DatePrecision;

/**
 * @extends Factory<Artwork>
 */
class ArtworkFactory extends Factory
{
    protected $model = Artwork::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $identifier = fake()->unique()->bothify('ART-####');

        return [
            'identifier' => $identifier,
            'slug' => fn (array $attributes): string => trim((string) preg_replace('/[^A-Za-z0-9_-]+/', '-', Str::ascii($attributes['identifier'] ?? $identifier)), '-'),
            'title' => ['en' => fake()->sentence(3)],
            'creation_date' => FuzzyDate::single('2000-01-01', DatePrecision::Year),
            'acquisition_method' => AcquisitionMethod::Donation,
            'acquisition_date' => FuzzyDate::single('2020-01-01', DatePrecision::Year),
        ];
    }
}
