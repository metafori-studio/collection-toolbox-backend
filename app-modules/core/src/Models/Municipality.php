<?php

namespace Metafori\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Metafori\Core\Database\Factories\MunicipalityFactory;
use Metafori\Core\Models\Concerns\Locality;
use Metafori\Core\Models\Contracts\Locality as LocalityContract;
use Spatie\Translatable\HasTranslations;
use Stringable;

class Municipality extends Model implements LocalityContract, Stringable
{
    use HasFactory, HasTranslations, Locality, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): MunicipalityFactory
    {
        return MunicipalityFactory::new();
    }

    protected $guarded = [];

    public array $translatable = [
        'name',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function municipalityParts(): HasMany
    {
        return $this->hasMany(MunicipalityPart::class);
    }
}
