<?php

namespace Metafori\Art\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Metafori\Art\Database\Factories\ArtworkAuthorFactory;
use Metafori\Core\Models\Person;

class ArtworkAuthor extends Model
{
    use HasFactory;

    protected $fillable = ['person_id', 'label', 'sort_order'];

    protected static function newFactory(): ArtworkAuthorFactory
    {
        return ArtworkAuthorFactory::new();
    }

    public function artwork(): BelongsTo
    {
        return $this->belongsTo(Artwork::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
