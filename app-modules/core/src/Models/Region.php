<?php

namespace Metafori\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Metafori\Core\Models\Contracts\Locality;
use Spatie\Translatable\HasTranslations;

class Region extends Model implements Locality
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $guarded = [];

    public array $translatable = [
        'name',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }
}
