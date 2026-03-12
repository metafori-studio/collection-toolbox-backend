<?php

namespace Metafori\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Metafori\Core\Models\Contracts\Locality;
use Spatie\Translatable\HasTranslations;

class Country extends Model implements Locality
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $guarded = [];

    public array $translatable = [
        'name',
    ];

    public function regions(): HasMany
    {
        return $this->hasMany(Region::class);
    }
}
