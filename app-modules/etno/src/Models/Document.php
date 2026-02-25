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
use Metafori\Etno\Enums\DocumentFormat;
use Metafori\Etno\Enums\DocumentNotation;
use Metafori\Etno\Enums\DocumentType;
use Spatie\Translatable\HasTranslations;

class Document extends Model
{
    use HasTranslations, SoftDeletes;

    protected $table = 'etno_documents';

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
            'type' => DocumentType::class,
            'language' => Language::class,
            'acquisition_method' => AcquisitionMethod::class,
            'collection_method' => CollectionMethod::class,
            'access_right' => AccessRight::class,
            'license' => License::class,
            'notations' => AsEnumCollection::of(DocumentNotation::class),
            'formats' => AsEnumCollection::of(DocumentFormat::class),
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'institution_id');
    }

    public function researchCollection(): BelongsTo
    {
        return $this->belongsTo(ResearchCollection::class, 'research_collection_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function localities(): BelongsToMany
    {
        return $this->belongsToMany(Locality::class, 'etno_document_locality');
    }

    public function authors(): HasMany
    {
        return $this->hasMany(DocumentAuthor::class, 'document_id')->orderBy('sort_order');
    }

    public function researchers(): HasMany
    {
        return $this->hasMany(DocumentResearcher::class, 'document_id')->orderBy('sort_order');
    }

    public function originators(): HasMany
    {
        return $this->hasMany(DocumentOriginator::class, 'document_id')->orderBy('sort_order');
    }

    public function keywords(): BelongsToMany
    {
        return $this->belongsToMany(Keyword::class, 'etno_document_keyword')->orderBy('sort_order');
    }
}
