<?php

namespace Metafori\Etno\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Metafori\Etno\Models\Item;
use Metafori\Etno\Support\ItemSearchSynchronizer;

class ItemPivot extends Pivot
{
    protected $touches = ['item'];

    protected static function booted(): void
    {
        static::deleted(function (ItemPivot $pivot): void {
            if ($pivot->item !== null) {
                app(ItemSearchSynchronizer::class)->syncItem($pivot->item);
            }
        });
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
