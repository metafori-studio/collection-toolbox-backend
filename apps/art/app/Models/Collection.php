<?php

namespace Metafori\Art\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Metafori\Art\Database\Factories\CollectionFactory;
use Spatie\Translatable\HasTranslations;

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
