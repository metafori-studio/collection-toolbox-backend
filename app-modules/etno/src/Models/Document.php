<?php

namespace Metafori\Etno\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Metafori\Core\Models\Keyword;
use Metafori\Core\Models\Organization;
use Metafori\Core\Models\Person;
use Metafori\Etno\Models\Concerns\HasDocumentMetadata;

class Document extends Model
{
    use HasDocumentMetadata, HasFactory, SoftDeletes;

    protected $table = 'etno_documents';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

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
        return $this->belongsToMany(Person::class, 'etno_document_authors')->orderByPivot('sort_order');
    }

    public function researchers(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'etno_document_researchers')->orderByPivot('sort_order');
    }

    public function originators(): HasMany
    {
        return $this->hasMany(DocumentOriginator::class)->orderBy('sort_order');
    }

    public function keywords(): BelongsToMany
    {
        return $this->belongsToMany(Keyword::class, 'etno_document_keyword')->orderByPivot('sort_order');
    }

    public function researchCollections(): BelongsToMany
    {
        return $this->belongsToMany(ResearchCollection::class, 'etno_document_research_collection')->orderByPivot('sort_order');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
}
