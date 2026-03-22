<?php

namespace Metafori\Etno\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Metafori\Core\Enums\DatePrecision;
use Metafori\Core\Enums\Language;
use Metafori\Core\Enums\License;
use Metafori\Core\Models\Country;
use Metafori\Core\Models\District;
use Metafori\Core\Models\Location;
use Metafori\Core\Models\Municipality;
use Metafori\Core\Models\MunicipalityPart;
use Metafori\Core\Models\Organization;
use Metafori\Core\Models\Region;
use Metafori\Etno\Enums\AccessRights;
use Metafori\Etno\Enums\AccrualMethod;
use Metafori\Etno\Enums\CollectionMethod;
use Metafori\Etno\Enums\ExtentUnit;
use Metafori\Etno\Enums\ItemType;
use Metafori\Etno\Enums\ProductionMethod;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Models\Project;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $buildDateRange = function (): array {
            $start = fake()->optional()->dateTimeBetween('-80 years', 'now');
            if (! $start) {
                return [null, null, null];
            }

            $isRange = fake()->boolean();
            $end = $isRange ? fake()->dateTimeBetween($start, 'now') : null;

            return [
                $start->format('Y-m-d'),
                $end?->format('Y-m-d'),
                [
                    'is_range' => $isRange,
                    'precision' => fake()->randomElement(DatePrecision::cases()),
                ],
            ];
        };

        [$timePeriodStart, $timePeriodEnd, $timePeriodSettings] = $buildDateRange();
        [$submissionStart, $submissionEnd, $submissionSettings] = $buildDateRange();
        [$publicationStart, $publicationEnd, $publicationSettings] = $buildDateRange();

        $localityClass = fake()->randomElement([
            Country::class,
            Region::class,
            District::class,
            Municipality::class,
            MunicipalityPart::class,
            Location::class,
        ]);

        return [
            'id' => Str::uuid()->toString(),
            'doi' => fake()->optional()->numerify('10.####/#######'),
            'type' => fake()->randomElement(ItemType::cases()),
            'extent' => fake()->optional()->randomFloat(2, 1, 100),
            'extent_unit' => fake()->optional()->randomElement(ExtentUnit::cases()),
            'language' => fake()->optional()->randomElement(Language::cases()),
            'collection_method' => fake()->optional()->randomElement(CollectionMethod::cases()),
            'accrual_method' => fake()->optional()->randomElement(AccrualMethod::cases()),
            'access_rights' => fake()->optional()->randomElement(AccessRights::cases()),
            'license' => fake()->optional()->randomElement(License::cases()),

            // Translatable fields
            'title' => fake()->optional()->passthrough(['en' => fake()->sentence()]),
            'subtitle' => fake()->optional()->passthrough(['en' => fake()->sentence()]),
            'abstract' => fake()->optional()->passthrough(['en' => fake()->paragraph()]),
            'general_note' => fake()->optional()->passthrough(['en' => fake()->paragraph()]),
            'terms_of_use' => fake()->optional()->passthrough(['en' => fake()->paragraph()]),
            'location_note' => fake()->optional()->passthrough(['en' => fake()->paragraph()]),
            'content_note' => fake()->optional()->passthrough(['en' => fake()->paragraph()]),
            'technical_note' => fake()->optional()->passthrough(['en' => fake()->paragraph()]),

            // Dates and settings
            'time_period_start' => $timePeriodStart,
            'time_period_end' => $timePeriodEnd,
            'time_period_settings' => $timePeriodSettings,

            'submission_date_start' => $submissionStart,
            'submission_date_end' => $submissionEnd,
            'submission_date_settings' => $submissionSettings,

            'publication_date_start' => $publicationStart,
            'publication_date_end' => $publicationEnd,
            'publication_date_settings' => $publicationSettings,

            // Enums array
            'production_methods' => fake()->optional()->randomElements(ProductionMethod::cases(), fake()->numberBetween(0, 2)),

            // Relations
            'locality_type' => (new $localityClass)->getMorphClass(),
            'locality_id' => $localityClass::factory(),
            'document_id' => Document::factory(),
            'project_id' => Project::factory(),
            'institution_id' => Organization::factory(),
        ];
    }
}
