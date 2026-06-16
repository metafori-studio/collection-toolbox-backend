<?php

namespace Metafori\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Metafori\Core\Database\Factories\MunicipalityPartFactory;
use Metafori\Core\Models\Concerns\Locality;
use Metafori\Core\Models\Contracts\Locality as LocalityContract;
use Spatie\Translatable\HasTranslations;
use Stringable;

class MunicipalityPart extends Model implements LocalityContract, Stringable
{
    use HasFactory, HasTranslations, Locality, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): MunicipalityPartFactory
    {
        return MunicipalityPartFactory::new();
    }

    protected $guarded = [];

    public array $translatable = [
        'name',
    ];

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }
}
