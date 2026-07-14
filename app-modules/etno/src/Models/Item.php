<?php

namespace Metafori\Etno\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Metafori\Core\Models\District;
use Metafori\Core\Models\Keyword;
use Metafori\Core\Models\Location;
use Metafori\Core\Models\Municipality;
use Metafori\Core\Models\MunicipalityPart;
use Metafori\Core\Models\Organization;
use Metafori\Core\Models\Person;
use Metafori\Core\Models\Region;
use Metafori\Etno\Database\Factories\ItemFactory;
use Metafori\Etno\Enums\AccessRights;
use Metafori\Etno\Enums\MediaType;
use Metafori\Etno\Models\Concerns\HasDocumentMetadata;
use Metafori\Etno\Models\Contracts\Inheritable;
use Metafori\Etno\Models\Pivots\ItemPivot;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Item extends Model implements HasMedia, Inheritable
{
    use HasDocumentMetadata, HasFactory, InteractsWithMedia, Searchable, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ItemFactory
    {
        return ItemFactory::new();
    }

    protected $table = 'etno_items';

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
        'languages',
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

    protected const array MEDIA_MIME_TYPES_MAP = [
        // Audio
        'audio/mpeg' => MediaType::Audio,
        'audio/wav' => MediaType::Audio,
        'audio/ogg' => MediaType::Audio,
        'audio/webm' => MediaType::Audio,
        'audio/aac' => MediaType::Audio,
        'audio/mp3' => MediaType::Audio,
        'audio/flac' => MediaType::Audio,
        'audio/m4a' => MediaType::Audio,
        'audio/opus' => MediaType::Audio,

        // Documents
        'application/pdf' => MediaType::Document,

        // Images
        'image/jpeg' => MediaType::Image,
        'image/png' => MediaType::Image,
        'image/gif' => MediaType::Image,
        'image/webp' => MediaType::Image,
        'image/svg+xml' => MediaType::Image,
        'image/bmp' => MediaType::Image,
        'image/tiff' => MediaType::Image,
        'image/avif' => MediaType::Image,
        'image/heic' => MediaType::Image,

        // Videos
        'video/mp4' => MediaType::Video,
        'video/webm' => MediaType::Video,
        'video/ogg' => MediaType::Video,
        'video/quicktime' => MediaType::Video,
        'video/mpeg' => MediaType::Video,
        'video/avi' => MediaType::Video,
        'video/mov' => MediaType::Video,
        'video/wmv' => MediaType::Video,
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

    public function identifier(): Attribute
    {
        return Attribute::get(
            fn ($value) => $value ?? (
                $this->document_id && $this->suffix
                    ? "{$this->document_id}:{$this->suffix}"
                    : null
            )
        );
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

    public function firstMedia(): MorphOne
    {
        return $this->media()
            ->one()
            ->ofMany('order_column', 'min');
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

    public function resolveInheritableAttribute(string $attribute): mixed
    {
        return $this->isInherited($attribute)
            ? $this->getParent()?->{$attribute}
            : $this->{$attribute};
    }

    public static function relations(): array
    {
        return self::documentRelations(Document::relations());
    }

    public static function documentRelations(array $with, ?\Closure $callback = null): array
    {
        return [
            ...$with,
            'document' => fn (BelongsTo $belongsTo) => tap($belongsTo->with($with), $callback),
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->isPublished();
    }

    public function isPublished(): bool
    {
        return \in_array(
            $this->resolveInheritableAttribute('access_rights'),
            AccessRights::published(),
        );
    }

    public function scopePublished(Builder $query): void
    {
        $query->where(function (Builder $query) {
            $query->where(function (Builder $q) {
                $q->whereJsonContains('etno_items.document_overrides', 'access_rights')
                    ->whereIn('etno_items.access_rights', AccessRights::published());
            })->orWhere(function (Builder $q) {
                $q->whereJsonDoesntContain('etno_items.document_overrides', 'access_rights')
                    ->whereHas('document', function (Builder $dq) {
                        $dq->published();
                    });
            });
        });
    }

    public function toSearchableArray(): array
    {
        $this->load(['media', ...self::relations()]);

        $resolveTranslations = function (string $attribute) {
            if ($this->isInherited($attribute)) {
                return $this->document?->getTranslations($attribute) ?? [];
            }

            return $this->getTranslations($attribute) ?? [];
        };

        $resolveLocalityHierarchy = function () {
            $locality = $this->resolveInheritableAttribute('locality');

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
            'type' => $this->resolveInheritableAttribute('type')?->value,
            'languages' => collect($this->resolveInheritableAttribute('languages'))->map->value->toArray(),
            'accrual_method' => $this->resolveInheritableAttribute('accrual_method')?->value,
            'collection_method' => $this->resolveInheritableAttribute('collection_method')?->value,
            'access_rights' => $this->resolveInheritableAttribute('access_rights')?->value,
            'license' => $this->resolveInheritableAttribute('license')?->value,
            'production_methods' => collect($this->resolveInheritableAttribute('production_methods'))->map->value->toArray(),
            'time_period' => \array_filter([
                'gte' => $this->resolveInheritableAttribute('time_period_start'),
                'lte' => $this->resolveInheritableAttribute('time_period_end'),
            ]),
            'author' => ['person_id' => $this->resolveInheritableAttribute('authors')->pluck('id')],
            'researcher' => ['person_id' => $this->resolveInheritableAttribute('researchers')->pluck('id')],
            'originator' => ['person_id' => $this->resolveInheritableAttribute('originators')->pluck('person_id')],
            'keyword' => ['id' => $this->resolveInheritableAttribute('keywords')->pluck('id')],
            'research_collection' => ['id' => $this->resolveInheritableAttribute('researchCollections')->pluck('id')],
            'institution' => ['id' => $this->resolveInheritableAttribute('institution_id')],
            'project' => ['id' => $this->resolveInheritableAttribute('project_id')],
            'transcripts' => $this->media
                ->pluck('custom_properties.transcripts.txt')
                ->filter()
                ->values()
                ->toArray(),
            ...$resolveLocalityHierarchy(),
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaType::Document->value)
            ->acceptsMimeTypes(self::allowedMimeTypesForCollection(MediaType::Document));

        $this->addMediaCollection(MediaType::Image->value)
            ->acceptsMimeTypes(self::allowedMimeTypesForCollection(MediaType::Image));

        $this->addMediaCollection(MediaType::Video->value)
            ->acceptsMimeTypes(self::allowedMimeTypesForCollection(MediaType::Video));

        $this->addMediaCollection(MediaType::Audio->value)
            ->acceptsMimeTypes(self::allowedMimeTypesForCollection(MediaType::Audio));
    }

    public function registerMediaConversions(?BaseMedia $media = null): void
    {
        $this->addMediaConversion('full')
            ->width(1280)
            ->height(1280)
            ->fit(Fit::Contain)
            ->performOnCollections(MediaType::Image->value);

        $this->addMediaConversion('thumbnail')
            ->width(300)
            ->height(300)
            ->fit(Fit::Crop)
            ->performOnCollections(MediaType::Image->value);
    }

    public static function allowedMimeTypesForCollection(MediaType $collection): array
    {
        return array_keys(array_filter(self::MEDIA_MIME_TYPES_MAP, fn (MediaType $value) => $value === $collection));
    }

    public function allowedMediaMimeTypes()
    {
        if ($this->media->isEmpty()) {
            return array_keys(self::MEDIA_MIME_TYPES_MAP);
        }

        return $this->media
            ->map(fn (Media $media) => $media->getType())
            ->filter()
            ->unique()
            ->flatMap(self::allowedMimeTypesForCollection(...));
    }

    public static function getMediaType(string $mimeType): ?MediaType
    {
        return self::MEDIA_MIME_TYPES_MAP[$mimeType] ?? null;
    }

    public static function getMediaCollectionName(string $mimeType): ?string
    {
        return self::getMediaType($mimeType)?->value;
    }
}
