<?php

namespace Metafori\Etno\Models;

use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
use Spatie\Translatable\HasTranslations;

class Item extends Model
{
    use HasTranslations, SoftDeletes;

    protected $table = 'etno_items';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    public array $translatable = [
        'title',
        'subtitle',
        'abstract',
        'general_note',
        'terms_of_use',
        'location_note',
        'content_note',
        'technical_note',
    ];

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

    public function localities(): HasMany
    {
        return $this->hasMany(ItemLocality::class, 'item_id')->orderBy('sort_order');
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
}
