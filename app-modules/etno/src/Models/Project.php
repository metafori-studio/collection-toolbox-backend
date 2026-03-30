<?php

namespace Metafori\Etno\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use Stringable;

class Project extends Model implements Stringable
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'etno_projects';

    protected $guarded = [];

    protected array $translatable = [
        'title',
    ];

    public function __toString(): string
    {
        return $this->title ?? '';
    }
}
