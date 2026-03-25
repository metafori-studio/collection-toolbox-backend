<?php

namespace Metafori\Etno\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Laravel\Scout\Searchable;
use Metafori\Core\Models\District;
use Metafori\Core\Models\Keyword;
use Metafori\Core\Models\Location;
use Metafori\Core\Models\Municipality;
use Metafori\Core\Models\MunicipalityPart;
use Metafori\Core\Models\Organization;
use Metafori\Core\Models\Person;
use Metafori\Core\Models\Region;
use Metafori\Etno\Models\Concerns\HasDocumentMetadata;
use Metafori\Etno\Models\Contracts\Inheritable;
use Metafori\Etno\Models\Pivots\ItemPivot;

class Item extends Model implements Inheritable
{
    use HasDocumentMetadata, HasFactory, Searchable, SoftDeletes;

    protected $table = 'etno_items';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $with = ['document'];

    protected $attributes = [
        'document_overrides' => '[]',
    ];

    public const array INHERITABLES = [
        'doi',
        'title',
        'subtitle',
        'abstract',
        'general_note',
        'terms_of_use',
        'location_note',
        'content_note',
        'technical_note',
        'type',
        'extents',
        'language',
        'accrual_method',
        'collection_method',
        'access_rights',
        'license',
        'production_methods',
        'time_period_start',
        'time_period_end',
        'time_period_settings',
        'submission_date_start',
        'submission_date_end',
        'submission_date_settings',
        'publication_date_start',
        'publication_date_end',
        'publication_date_settings',
        'institution_id',
        'project_id',
        'locality',
        'authors',
        'researchers',
        'originators',
        'keywords',
        'researchCollections',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(
            'document',
            fn (Builder $builder) => $builder->whereHas('document')
        );
    }

    public function casts(): array
    {
        return [
            'document_overrides' => 'array',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function locality(): MorphTo
    {
        return $this->morphTo();
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'etno_item_authors')
            ->using(ItemPivot::class)
            ->orderByPivot('sort_order');
    }

    public function researchers(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'etno_item_researchers')
            ->using(ItemPivot::class)
            ->orderByPivot('sort_order');
    }

    public function originators(): HasMany
    {
        return $this->hasMany(ItemOriginator::class)->orderBy('sort_order');
    }

    public function keywords(): BelongsToMany
    {
        return $this->belongsToMany(Keyword::class, 'etno_item_keyword')
            ->using(ItemPivot::class)
            ->orderByPivot('sort_order');
    }

    public function researchCollections(): BelongsToMany
    {
        return $this->belongsToMany(ResearchCollection::class, 'etno_item_research_collection')
            ->using(ItemPivot::class)
            ->orderByPivot('sort_order');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function isInheritable(string $attribute): bool
    {
        return \in_array($attribute, self::INHERITABLES);
    }

    public function isInherited(string $attribute): bool
    {
        if (! $this->isInheritable($attribute)) {
            throw new \LogicException("Attribute {$attribute} is not inheritable.");
        }

        return ! \in_array($attribute, $this->document_overrides);
    }

    public function isInheritableAndInherited(string $attribute): bool
    {
        return $this->isInheritable($attribute) && $this->isInherited($attribute);
    }

    public function getParent(): ?Document
    {
        return $this->document;
    }

    public static function relations(): array
    {
        return self::documentRelations([
            'institution',
            'project',
            'authors',
            'researchers',
            'originators.person',
            'keywords',
            'researchCollections',
            ...self::localityRelations(),
        ]);
    }

    public static function localityRelations(): array
    {
        $morphWith = [
            Region::class => ['country'],
            District::class => ['region.country'],
            Municipality::class => ['district.region.country'],
            MunicipalityPart::class => ['municipality.district.region.country'],
        ];

        return [
            'locality' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                ...$morphWith,
                Location::class => [
                    'parent' => fn (MorphTo $morphTo) => $morphTo->morphWith($morphWith),
                ],
            ]),
        ];
    }

    public static function documentRelations(array $with, ?\Closure $callback = null): array
    {
        return [
            ...$with,
            'document' => fn (BelongsTo $belongsTo) => tap($belongsTo->with($with), $callback),
        ];
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing(self::relations());

        $resolveRelationIds = function (string $relation, string $key = 'id') {
            $value = $this->isInheritableAndInherited($relation)
                ? $this->getParent()?->{$relation}
                : $this->{$relation};

            if ($value === null) {
                return [];
            }

            if ($value instanceof Collection) {
                return $value->pluck($key)->toArray();
            }

            return [$value->{$key}];
        };

        $resolveValue = function (string $attribute) {
            return $this->isInheritableAndInherited($attribute)
                ? $this->document?->{$attribute}
                : $this->{$attribute};
        };

        $resolveTranslations = function (string $attribute) {
            if ($this->isInheritableAndInherited($attribute)) {
                return $this->document?->getTranslations($attribute) ?? [];
            }

            return $this->getTranslations($attribute) ?? [];
        };

        $resolveLocalityHierarchy = function () {
            $locality = $this->isInheritableAndInherited('locality')
                ? $this->getParent()?->locality
                : $this->locality;

            if (! $locality) {
                return [];
            }

            $localities = [];

            $current = $locality;
            while ($current) {
                $type = $current->getMorphClass();
                $localities[$type][] = ['id' => $current->id];

                $current = match (true) {
                    $current instanceof MunicipalityPart => $current->municipality,
                    $current instanceof Municipality => $current->district,
                    $current instanceof District => $current->region,
                    $current instanceof Region => $current->country,
                    $current instanceof Location => $current->parent,
                    default => null,
                };
            }

            return $localities;
        };

        return [
            'id' => $this->id,
            'document_id' => $this->document_id,
            'title' => $resolveTranslations('title'),
            'subtitle' => $resolveTranslations('subtitle'),
            'abstract' => $resolveTranslations('abstract'),
            'general_note' => $resolveTranslations('general_note'),
            'terms_of_use' => $resolveTranslations('terms_of_use'),
            'location_note' => $resolveTranslations('location_note'),
            'content_note' => $resolveTranslations('content_note'),
            'technical_note' => $resolveTranslations('technical_note'),
            'type' => $resolveValue('type')?->value,
            'language' => $resolveValue('language')?->value,
            'accrual_method' => $resolveValue('accrual_method')?->value,
            'collection_method' => $resolveValue('collection_method')?->value,
            'access_rights' => $resolveValue('access_rights')?->value,
            'license' => $resolveValue('license')?->value,
            'production_methods' => collect($resolveValue('production_methods'))->map->value->toArray(),
            'author' => ['person_id' => $resolveRelationIds('authors')],
            'researcher' => ['person_id' => $resolveRelationIds('researchers')],
            'originator' => ['person_id' => $resolveRelationIds('originators', 'person_id')],
            'keyword' => ['id' => $resolveRelationIds('keywords')],
            'research_collection' => ['id' => $resolveRelationIds('researchCollections')],
            'institution' => ['id' => $resolveRelationIds('institution')],
            'project' => ['id' => $resolveRelationIds('project')],
            ...$resolveLocalityHierarchy(),
        ];
    }
}
