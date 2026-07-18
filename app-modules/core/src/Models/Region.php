<?php

namespace Metafori\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Metafori\Core\Database\Factories\RegionFactory;
use Metafori\Core\Models\Concerns\Locality;
use Metafori\Core\Models\Contracts\Locality as LocalityContract;
use Spatie\Translatable\HasTranslations;
use Stringable;

class Region extends Model implements LocalityContract, Stringable
{
    use HasFactory, HasTranslations, Locality;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): RegionFactory
    {
        return RegionFactory::new();
    }

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
