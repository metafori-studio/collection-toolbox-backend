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
use Metafori\Core\Models\Locality;
use Metafori\Core\Models\Organization;
use Metafori\Etno\Enums\AccessRight;
use Metafori\Etno\Enums\AcquisitionMethod;
use Metafori\Etno\Enums\CollectionMethod;
use Metafori\Etno\Enums\ItemFormat;
use Metafori\Etno\Enums\ItemNotation;
use Metafori\Etno\Enums\ItemType;
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
        'note',
        'access_right_note',
        'locality_note',
    ];

    public function casts(): array
    {
        return [
            'study_period_start' => 'date',
            'study_period_end' => 'date',
            'study_period_settings' => 'json',
            'submission_date_start' => 'date',
            'submission_date_end' => 'date',
            'submission_date_settings' => 'json',
            'type' => ItemType::class,
            'language' => Language::class,
            'acquisition_method' => AcquisitionMethod::class,
            'collection_method' => CollectionMethod::class,
            'access_right' => AccessRight::class,
            'license' => License::class,
            'notations' => AsEnumCollection::of(ItemNotation::class),
            'formats' => AsEnumCollection::of(ItemFormat::class),
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

    public function localities(): BelongsToMany
    {
        return $this->belongsToMany(Locality::class, 'etno_item_locality');
    }

    public function authors(): HasMany
    {
        return $this->hasMany(ItemAuthor::class, 'item_id')->orderBy('sort_order');
    }

    public function researchers(): HasMany
    {
        return $this->hasMany(ItemResearcher::class, 'item_id')->orderBy('sort_order');
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
