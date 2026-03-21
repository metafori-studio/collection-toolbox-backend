<?php

namespace Metafori\Etno\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Metafori\Core\Models\Person;
use Spatie\Translatable\HasTranslations;

class ItemOriginator extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'etno_item_originators';

    protected $guarded = [];

    protected array $translatable = ['label'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
