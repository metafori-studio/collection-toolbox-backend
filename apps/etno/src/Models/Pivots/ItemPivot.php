<?php

namespace Metafori\Etno\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Metafori\Etno\Models\Item;

class ItemPivot extends Pivot
{
    protected $touches = ['item'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
