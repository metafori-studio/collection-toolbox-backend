<?php

namespace Metafori\Etno\Models\Concerns;

use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Metafori\Core\Enums\Language;
use Metafori\Core\Enums\License;
use Metafori\Etno\Casts\ExtentCollectionCast;
use Metafori\Etno\Enums\AccessRights;
use Metafori\Etno\Enums\AccrualMethod;
use Metafori\Etno\Enums\CollectionMethod;
use Metafori\Etno\Enums\ItemType;
use Metafori\Etno\Enums\ProductionMethod;
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
            'time_period_settings' => 'json',
            'submission_date_start' => 'date',
            'submission_date_end' => 'date',
            'submission_date_settings' => 'json',
            'publication_date_start' => 'date',
            'publication_date_end' => 'date',
            'publication_date_settings' => 'json',
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
}
