<?php

namespace Metafori\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Metafori\Core\Models\Concerns\Locality;
use Metafori\Core\Models\Contracts\Locality as LocalityContract;
use Spatie\Translatable\HasTranslations;
use Stringable;

class Location extends Model implements LocalityContract, Stringable
{
    use HasFactory, HasTranslations, Locality, SoftDeletes;

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
