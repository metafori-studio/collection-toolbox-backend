<?php

namespace Metafori\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Metafori\Core\Models\Concerns\Locality;
use Metafori\Core\Models\Contracts\Locality as LocalityContract;
use Spatie\Translatable\HasTranslations;

class MunicipalityPart extends Model implements LocalityContract
{
    use HasFactory, HasTranslations, Locality, SoftDeletes;

    protected $guarded = [];

    public array $translatable = [
        'name',
    ];

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }
}
