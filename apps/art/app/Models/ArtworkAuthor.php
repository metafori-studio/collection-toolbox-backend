<?php

namespace Metafori\Art\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Metafori\Art\Database\Factories\ArtworkAuthorFactory;
use Metafori\Core\Models\Person;

/**
 * @property int $id
 * @property int $artwork_id
 * @property int|null $person_id
 * @property string|null $label
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Artwork|null $artwork
 * @property-read Person|null $person
 */
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
