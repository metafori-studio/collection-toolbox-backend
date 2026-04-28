<?php

namespace Metafori\Etno\Support;

use Metafori\Core\Enums\Language;
use Metafori\Core\Enums\License;
use Metafori\Core\Models\Country;
use Metafori\Core\Models\District;
use Metafori\Core\Models\Keyword;
use Metafori\Core\Models\Location;
use Metafori\Core\Models\Municipality;
use Metafori\Core\Models\MunicipalityPart;
use Metafori\Core\Models\Organization;
use Metafori\Core\Models\Person;
use Metafori\Core\Models\Region;
use Metafori\Etno\Enums\AccessRights;
use Metafori\Etno\Enums\AccrualMethod;
use Metafori\Etno\Enums\CollectionMethod;
use Metafori\Etno\Enums\ItemType;
use Metafori\Etno\Enums\ProductionMethod;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Models\Project;
use Metafori\Etno\Models\ResearchCollection;

class FacetMetadata
{
    public const array ENUM_MAPPING = [
        'access_rights' => AccessRights::class,
        'accrual_method' => AccrualMethod::class,
        'collection_method' => CollectionMethod::class,
        'language' => Language::class,
        'license' => License::class,
        'production_methods' => ProductionMethod::class,
        'type' => ItemType::class,
    ];

    public const array MODEL_MAPPING = [
        'document_id' => Document::class,
        'author.person_id' => Person::class,
        'country.id' => Country::class,
        'district.id' => District::class,
        'institution.id' => Organization::class,
        'keyword.id' => Keyword::class,
        'location.id' => Location::class,
        'municipality_part.id' => MunicipalityPart::class,
        'municipality.id' => Municipality::class,
        'originator.person_id' => Person::class,
        'project.id' => Project::class,
        'region.id' => Region::class,
        'research_collection.id' => ResearchCollection::class,
        'researcher.person_id' => Person::class,
    ];

    public static function enums(): array
    {
        return array_keys(self::ENUM_MAPPING);
    }

    public static function models(): array
    {
        return array_keys(self::MODEL_MAPPING);
    }

    public static function all(): array
    {
        return [
            ...self::enums(),
            ...self::models(),
        ];
    }
}
