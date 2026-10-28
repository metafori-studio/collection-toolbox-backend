<?php

namespace Metafori\Art\Support;

use BackedEnum;
use Filament\Support\Contracts\HasLabel;
use Metafori\Art\Enums\AcquisitionMethod;
use Metafori\Art\Enums\Medium;
use Metafori\Art\Enums\ObjectType;
use Metafori\Art\Enums\Technique;
use Metafori\Art\Enums\WorkType;
use Metafori\Art\Models\Collection;
use Metafori\Core\Models\Person;

class FacetMetadata
{
    /**
     * @var array<string, class-string<BackedEnum&HasLabel>>
     */
    public const array ENUM_MAPPING = [
        'work_types' => WorkType::class,
        'object_types' => ObjectType::class,
        'mediums' => Medium::class,
        'techniques' => Technique::class,
        'acquisition_method' => AcquisitionMethod::class,
    ];

    public const array MODEL_MAPPING = [
        'author.person_id' => Person::class,
        'collection.id' => Collection::class,
    ];

    /**
     * @return list<string>
     */
    public static function enums(): array
    {
        return array_keys(self::ENUM_MAPPING);
    }

    /**
     * @return list<string>
     */
    public static function models(): array
    {
        return array_keys(self::MODEL_MAPPING);
    }

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            ...self::enums(),
            ...self::models(),
            'author.label',
        ];
    }
}
