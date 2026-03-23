<?php

namespace Metafori\Etno\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Metafori\Core\Enums\Language;
use Metafori\Core\Enums\License;
use Metafori\Core\Models\Keyword;
use Metafori\Core\Models\Organization;
use Metafori\Core\Models\Person;
use Metafori\Etno\Enums\AccessRights;
use Metafori\Etno\Enums\AccrualMethod;
use Metafori\Etno\Enums\CollectionMethod;
use Metafori\Etno\Enums\ExtentUnit;
use Metafori\Etno\Enums\ItemType;
use Metafori\Etno\Enums\ProductionMethod;
use Metafori\Etno\Models\Contracts\Inheritable;
use Spatie\Translatable\HasTranslations;

class Item extends Model implements Inheritable
{
    use HasFactory, HasTranslations, SoftDeletes;

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
        'extent',
        'extent_unit',
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
        'institution',
        'project',
        'locality',
        'authors',
        'researchers',
        'originators',
        'keywords',
        'researchCollections',
    ];

    protected array $translatable = [
        'title',
        'subtitle',
        'abstract',
        'general_note',
        'terms_of_use',
        'location_note',
        'content_note',
        'technical_note',
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
            'language' => Language::class,
            'accrual_method' => AccrualMethod::class,
            'collection_method' => CollectionMethod::class,
            'access_rights' => AccessRights::class,
            'license' => License::class,
            'production_methods' => AsEnumCollection::of(ProductionMethod::class),
            'extent_unit' => ExtentUnit::class,
            'document_overrides' => 'array',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'institution_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function locality(): MorphTo
    {
        return $this->morphTo();
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'etno_item_authors')->orderByPivot('sort_order');
    }

    public function researchers(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'etno_item_researchers')->orderByPivot('sort_order');
    }

    public function originators(): HasMany
    {
        return $this->hasMany(ItemOriginator::class, 'item_id')->orderBy('sort_order');
    }

    public function keywords(): BelongsToMany
    {
        return $this->belongsToMany(Keyword::class, 'etno_item_keyword')->orderByPivot('sort_order');
    }

    public function researchCollections(): BelongsToMany
    {
        return $this->belongsToMany(ResearchCollection::class, 'etno_item_research_collection')->orderByPivot('sort_order');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
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

    public function getParentValue(string $attribute, ?string $locale = null, bool $useFallbackLocale = true): mixed
    {
        if (! $parent = $this->getParent()) {
            throw new \LogicException('No parent document found.');
        }

        if ($locale && $parent->isTranslatableAttribute($attribute)) {
            return $parent->getTranslation($attribute, $locale, $useFallbackLocale);
        }

        return $parent->{$attribute};
    }

    public function getParent(): ?Document
    {
        return $this->document;
    }
}
