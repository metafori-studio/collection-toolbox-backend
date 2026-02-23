<?php

namespace Metafori\Etno\Models;

use Illuminate\Database\Eloquent\Model;
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
}
