<?php

namespace Metafori\Etno\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Project extends Model
{
    use HasTranslations, SoftDeletes;

    protected $table = 'etno_projects';

    protected $guarded = [];

    public $translatable = [
        'title',
    ];
}
