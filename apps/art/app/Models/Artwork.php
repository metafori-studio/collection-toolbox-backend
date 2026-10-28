<?php

namespace Metafori\Art\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

class Artwork extends Model implements HasMedia
{
    use HasFactory, HasFuzzyDates, HasTranslations, InteractsWithMedia;

    protected $guarded = [];

    protected static function newFactory(): ArtworkFactory
    {
        return ArtworkFactory::new();
    }

    protected array $translatable = ['title', 'description', 'dating'];

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
        $this->addMediaCollection('images')
            ->useDisk(config('art.media_disk', 'public'));
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
