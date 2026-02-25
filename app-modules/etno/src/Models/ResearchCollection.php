<?php

namespace Metafori\Etno\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class ResearchCollection extends Model
{
    use HasTranslations, SoftDeletes;

    protected $table = 'etno_research_collections';

    protected $guarded = [];

    public $translatable = [
        'title',
    ];

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'etno_document_research_collection')->orderByPivot('sort_order');
    }
}
