<?php

namespace Metafori\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Metafori\Core\Enums\LocalityType;
use Spatie\Translatable\HasTranslations;

class Locality extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'name',
        'latitude',
        'longitude',
        'type',
    ];

    public array $translatable = [
        'name',
    ];

    protected $casts = [
        'type' => LocalityType::class,
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Locality::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Locality::class, 'parent_id');
    }
}
