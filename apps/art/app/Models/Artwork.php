<?php

namespace Metafori\Art\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Laravel\Scout\Searchable;
use Metafori\Art\Data\Measurement;
use Metafori\Art\Database\Factories\ArtworkFactory;
use Metafori\Art\Enums\AcquisitionMethod;
use Metafori\Art\Enums\Medium;
use Metafori\Art\Enums\ObjectType;
use Metafori\Art\Enums\Technique;
use Metafori\Art\Enums\WorkType;
use Metafori\Core\Data\FuzzyDate;
use Metafori\Core\Traits\HasFuzzyDates;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media as SpatieMedia;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string|null $identifier
 * @property string $slug
 * @property string|null $title
 * @property string|null $description
 * @property string|null $dating
 * @property FuzzyDate|null $creation_date
 * @property Carbon|null $creation_date_start
 * @property Carbon|null $creation_date_end
 * @property \Illuminate\Support\Collection<int, WorkType>|null $work_types
 * @property \Illuminate\Support\Collection<int, ObjectType>|null $object_types
 * @property \Illuminate\Support\Collection<int, Medium>|null $mediums
 * @property \Illuminate\Support\Collection<int, Technique>|null $techniques
 * @property AcquisitionMethod|null $acquisition_method
 * @property FuzzyDate|null $acquisition_date
 * @property \Illuminate\Support\Collection<int, Measurement>|null $measurements
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ArtworkAuthor> $authors
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Collection> $collections
 */
class Artwork extends Model implements HasMedia
{
    use HasFactory, HasFuzzyDates, HasTranslations, InteractsWithMedia, Searchable;

    protected $guarded = [];

    protected static function newFactory(): ArtworkFactory
    {
        return ArtworkFactory::new();
    }

    protected array $translatable = ['title', 'description', 'dating'];

    public function toSearchableArray(): array
    {
        $resolveTranslations = function (string $attribute): array {
            $translations = $this->getTranslations($attribute);

            return collect(config('app.locales'))
                ->mapWithKeys(fn (string $locale) => [$locale => $translations[$locale] ?? null])
                ->toArray();
        };

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'identifier' => $this->identifier,
            'title' => $resolveTranslations('title'),
            'description' => $resolveTranslations('description'),
            'dating' => $resolveTranslations('dating'),
            'work_types' => $this->work_types?->map->value->values()->all() ?? [],
            'object_types' => $this->object_types?->map->value->values()->all() ?? [],
            'mediums' => $this->mediums?->map->value->values()->all() ?? [],
            'techniques' => $this->techniques?->map->value->values()->all() ?? [],
            'acquisition_method' => $this->acquisition_method?->value,
            'creation_date' => array_filter([
                'gte' => $this->creation_date_start?->format('Y-m-d'),
                'lte' => $this->creation_date_end?->format('Y-m-d'),
            ]),
            'author' => [
                'person_id' => $this->authors->pluck('person_id')->filter()->values()->all(),
                'label' => $this->authors->whereNull('person_id')->pluck('label')->filter()->values()->all(),
            ],
            'collection' => [
                'id' => $this->collections->pluck('id')->values()->all(),
            ],
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function casts(): array
    {
        return [
            'creation_date' => FuzzyDate::class,
            'work_types' => AsEnumCollection::of(WorkType::class),
            'object_types' => AsEnumCollection::of(ObjectType::class),
            'mediums' => AsEnumCollection::of(Medium::class),
            'techniques' => AsEnumCollection::of(Technique::class),
            'acquisition_method' => AcquisitionMethod::class,
            'acquisition_date' => FuzzyDate::class,
            'measurements' => AsCollection::of(Measurement::class),
        ];
    }

    public function authors(): HasMany
    {
        return $this->hasMany(ArtworkAuthor::class)->orderBy('sort_order');
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(Collection::class, 'artwork_collection')->orderByPivot('sort_order');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }

    public function registerMediaConversions(?SpatieMedia $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(800)
            ->height(800)
            ->format('webp');

        $this->addMediaConversion('web')
            ->width(2048)
            ->height(2048)
            ->format('webp');
    }
}
