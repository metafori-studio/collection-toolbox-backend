<?php

namespace Metafori\Etno\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class ResearchCollection extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'etno_research_collections';

    protected $guarded = [];

    public $translatable = [
        'title',
    ];

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'etno_item_research_collection')->orderByPivot('sort_order');
    }
}
