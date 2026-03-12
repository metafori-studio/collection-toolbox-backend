<?php

namespace Metafori\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Metafori\Core\Models\Contracts\Locality;
use Spatie\Translatable\HasTranslations;

class Location extends Model implements Locality
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = 'locations';

    protected $guarded = [];

    public array $translatable = [
        'name',
    ];

    public function parent(): MorphTo
    {
        return $this->morphTo();
    }
}
