<?php

namespace Metafori\Art\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Metafori\Art\Database\Factories\CollectionFactory;
use Metafori\Core\Data\FuzzyDate;
use Metafori\Core\Enums\DatePrecision;
use Spatie\Translatable\HasTranslations;
use Stringable;

/**
 * @property int $id
 * @property int|null $collection_category_id
 * @property string|null $title
 * @property Carbon|null $published_at
 * @property bool $is_featured
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $artworks_count
 * @property-read FuzzyDate|null $datespan
 * @property-read CollectionCategory|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Artwork> $artworks
 */
class Collection extends Model implements Stringable
{
    use HasFactory, HasTranslations;

    protected $fillable = ['collection_category_id', 'title', 'description', 'summary', 'author', 'is_featured', 'published_at'];

    protected array $translatable = ['title', 'description', 'summary', 'author'];

    public function __toString(): string
    {
        return $this->title ?? '';
    }

    protected function casts(): array
    {
        return ['is_featured' => 'boolean', 'published_at' => 'datetime'];
    }

    protected static function newFactory(): CollectionFactory
    {
        return CollectionFactory::new();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CollectionCategory::class, 'collection_category_id');
    }

    public function previewArtworks(): BelongsToMany
    {
        return $this->artworks()
            ->whereHas('media', fn ($query) => $query->where('collection_name', 'images'))
            ->with(['media' => fn ($query) => $query->where('collection_name', 'images')])
            ->limit(8);
    }

    /**
     * @return BelongsToMany<Artwork, $this>
     */
    public function artworks(): BelongsToMany
    {
        return $this->belongsToMany(Artwork::class, 'artwork_collection')->orderByPivot('sort_order');
    }

    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope the query to include the minimum and maximum artwork creation dates.
     */
    public function scopeWithDatespan(Builder $query): void
    {
        $query
            ->withMin('artworks as artworks_min_creation_date', DB::raw('COALESCE(creation_date_start, creation_date_end)'))
            ->withMax('artworks as artworks_max_creation_date', DB::raw('COALESCE(creation_date_end, creation_date_start)'));
    }

    /**
     * Determine whether the datespan aggregates have been loaded on the model.
     */
    public function hasDatespan(): bool
    {
        return \array_key_exists('artworks_min_creation_date', $this->attributes)
            && \array_key_exists('artworks_max_creation_date', $this->attributes);
    }

    /**
     * Compute the collection datespan from the minimum and maximum artwork creation years.
     */
    protected function datespan(): Attribute
    {
        return Attribute::get(fn (): ?FuzzyDate => self::makeDatespan(
            $this->attributes['artworks_min_creation_date'] ?? null,
            $this->attributes['artworks_max_creation_date'] ?? null,
        ));
    }

    private static function makeDatespan(?string $start, ?string $end): ?FuzzyDate
    {
        if ($start === null && $end === null) {
            return null;
        }

        $startYear = Carbon::parse($start ?? $end)->year;
        $endYear = Carbon::parse($end ?? $start)->year;

        return $startYear === $endYear
            ? FuzzyDate::single("{$startYear}-01-01", DatePrecision::Year)
            : FuzzyDate::range("{$startYear}-01-01", DatePrecision::Year, "{$endYear}-01-01", DatePrecision::Year);
    }
}
