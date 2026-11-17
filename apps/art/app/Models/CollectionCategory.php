<?php

namespace Metafori\Art\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Metafori\Art\Database\Factories\CollectionCategoryFactory;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string|null $title
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Collection> $collections
 */
class CollectionCategory extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['title'];

    protected array $translatable = ['title'];

    protected static function newFactory(): CollectionCategoryFactory
    {
        return CollectionCategoryFactory::new();
    }

    public function collections(): HasMany
    {
        return $this->hasMany(Collection::class);
    }
}
