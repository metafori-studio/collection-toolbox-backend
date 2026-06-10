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
use Metafori\Core\Models\District;
use Metafori\Core\Models\Keyword;
use Metafori\Core\Models\Location;
use Metafori\Core\Models\Municipality;
use Metafori\Core\Models\MunicipalityPart;
use Metafori\Core\Models\Organization;
use Metafori\Core\Models\Person;
use Metafori\Core\Models\Region;
use Metafori\Etno\Database\Factories\DocumentFactory;
use Metafori\Etno\Enums\AccessRights;
use Metafori\Etno\Models\Concerns\HasDocumentMetadata;
use Stringable;

class Document extends Model implements Stringable
{
    use HasDocumentMetadata, HasFactory, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): DocumentFactory
    {
        return DocumentFactory::new();
    }

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
        return $this->hasMany(Item::class)
            ->orderByRaw('LENGTH(suffix)')
            ->orderBy('suffix');
    }

    public function generateNextSequenceSuffix(): string
    {
        $maxSuffix = $this->items()
            ->pluck('suffix')
            ->last();

        if ($maxSuffix === null) {
            return 'a';
        }

        return self::incrementSuffix($maxSuffix);
    }

    public static function incrementSuffix(string $suffix): string
    {
        return str_increment($suffix);
    }

    public function scopePublished(Builder $query): void
    {
        $query->whereIn('access_rights', AccessRights::published());
    }

    public static function relations(): array
    {
        return [
            'institution',
            'project',
            'authors',
            'researchers',
            'originators.person',
            'keywords',
            'researchCollections',
            ...self::localityRelations(),
        ];
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

    public function __toString(): string
    {
        return $this->id;
    }
}
