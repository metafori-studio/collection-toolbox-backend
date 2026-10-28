<?php

namespace Metafori\Art\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Metafori\Art\Database\Factories\CollectionFactory;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string|null $title
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $artworks_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Artwork> $artworks
 */
class Collection extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['title'];

    protected array $translatable = ['title'];

    protected static function newFactory(): CollectionFactory
    {
        return CollectionFactory::new();
    }

    public function artworks(): BelongsToMany
    {
        return $this->belongsToMany(Artwork::class, 'artwork_collection')->orderByPivot('sort_order');
    }
}
