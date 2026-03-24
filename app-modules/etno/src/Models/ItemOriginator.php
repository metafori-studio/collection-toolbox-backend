<?php

namespace Metafori\Etno\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Metafori\Etno\Models\Concerns\Originator;

class ItemOriginator extends Model
{
    use Originator;

    protected $table = 'etno_item_originators';

    protected $guarded = [];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
