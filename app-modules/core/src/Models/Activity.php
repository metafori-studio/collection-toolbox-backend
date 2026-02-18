<?php

namespace Metafori\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    protected $table = 'activities';

    protected $fillable = [
        'activity_number',
        'activity_year_start',
        'activity_year_end',
        'activity_type',
        'details_type',
        'details_id',
    ];

    public function details(): MorphTo
    {
        return $this->morphTo();
    }
}
