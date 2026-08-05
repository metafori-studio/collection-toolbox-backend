<?php

namespace Metafori\Etno\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Metafori\Core\Enums\DatePrecision;
use Metafori\Core\Enums\Language;
use Metafori\Core\Enums\License;
use Metafori\Etno\Casts\ExtentCollectionCast;
use Metafori\Etno\Enums\AccessRights;
use Metafori\Etno\Enums\AccrualMethod;
use Metafori\Etno\Enums\CollectionMethod;
use Metafori\Etno\Enums\ItemType;
use Metafori\Etno\Enums\ProductionMethod;
use Metafori\Etno\Models\PrecisionDate;
use Spatie\Translatable\HasTranslations;

trait HasDocumentMetadata
{
    use HasTranslations;

    protected function getTranslatableAttributes(): array
    {
        return [
            'title',
            'subtitle',
            'abstract',
            'general_note',
            'terms_of_use',
            'location_note',
            'content_note',
            'technical_note',
        ];
    }

    protected function initializeHasDocumentMetadata(): void
    {
        $this->mergeCasts([
            'time_period_start' => 'date',
            'time_period_end' => 'date',
            'time_period_settings' => 'array',
            'submission_date_start' => 'date',
            'submission_date_end' => 'date',
            'submission_date_settings' => 'array',
            'publication_date_start' => 'date',
            'publication_date_end' => 'date',
            'publication_date_settings' => 'array',
            'type' => ItemType::class,
            'languages' => AsEnumCollection::of(Language::class),
            'accrual_method' => AccrualMethod::class,
            'collection_method' => CollectionMethod::class,
            'access_rights' => AccessRights::class,
            'license' => License::class,
            'production_methods' => AsEnumCollection::of(ProductionMethod::class),
            'extents' => ExtentCollectionCast::class,
        ]);
    }

    protected function timePeriod(): Attribute
    {
        return Attribute::make(
            get: fn (): ?PrecisionDate => $this->createPrecisionDate(
                $this->time_period_start,
                $this->time_period_end,
                $this->time_period_settings,
            ),
        );
    }

    protected function submissionDate(): Attribute
    {
        return Attribute::make(
            get: fn (): ?PrecisionDate => $this->createPrecisionDate(
                $this->submission_date_start,
                $this->submission_date_end,
                $this->submission_date_settings,
            ),
        );
    }

    protected function publicationDate(): Attribute
    {
        return Attribute::make(
            get: fn (): ?PrecisionDate => $this->createPrecisionDate(
                $this->publication_date_start,
                $this->publication_date_end,
                $this->publication_date_settings,
            ),
        );
    }

    private function createPrecisionDate($start, $end, ?array $settings): ?PrecisionDate
    {
        if ($start === null && $end === null) {
            return null;
        }

        $precision = $settings['precision'] ?? null;

        return new PrecisionDate(
            start: $start,
            end: $end,
            is_range: (bool) ($settings['is_range'] ?? false),
            precision: $precision instanceof DatePrecision
                ? $precision
                : ($precision ? DatePrecision::tryFrom($precision) : null),
        );
    }
}
