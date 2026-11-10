<?php

namespace Metafori\Art\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Metafori\Art\Data\Measurement;
use Metafori\Art\Enums\AcquisitionMethod;
use Metafori\Art\Enums\MeasurementType;
use Metafori\Art\Enums\MeasurementUnit;
use Metafori\Art\Enums\Medium;
use Metafori\Art\Enums\ObjectType;
use Metafori\Art\Enums\Technique;
use Metafori\Art\Enums\WorkType;
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
        $creationYear = fake()->numberBetween(1900, 2020);
        $acquisitionYear = fake()->numberBetween($creationYear, 2025);

        return [
            'identifier' => $identifier,
            'slug' => fn (array $attributes): string => trim((string) preg_replace('/[^A-Za-z0-9_-]+/', '-', Str::ascii($attributes['identifier'] ?? $identifier)), '-'),
            'title' => [
                'cs' => fake()->sentence(3),
                'en' => fake()->sentence(3),
            ],
            'description' => [
                'cs' => fake()->paragraph(),
                'en' => fake()->paragraph(),
            ],
            'dating' => [
                'cs' => "kolem {$creationYear}",
                'en' => "circa {$creationYear}",
            ],
            'creation_date' => FuzzyDate::single("{$creationYear}-01-01", DatePrecision::Year),
            'work_types' => fake()->randomElements(WorkType::cases(), fake()->numberBetween(1, 2)),
            'object_types' => fake()->randomElements(ObjectType::cases(), 1),
            'mediums' => fake()->randomElements(Medium::cases(), fake()->numberBetween(1, 2)),
            'techniques' => fake()->randomElements(Technique::cases(), fake()->numberBetween(1, 3)),
            'acquisition_method' => fake()->randomElement(AcquisitionMethod::cases()),
            'acquisition_date' => FuzzyDate::single("{$acquisitionYear}-01-01", DatePrecision::Year),
            'measurements' => [
                new Measurement([
                    'type' => MeasurementType::Height,
                    'value' => fake()->randomFloat(1, 10, 200),
                    'unit' => MeasurementUnit::Cm,
                ]),
                new Measurement([
                    'type' => MeasurementType::Width,
                    'value' => fake()->randomFloat(1, 10, 200),
                    'unit' => MeasurementUnit::Cm,
                ]),
            ],
        ];
    }
}
