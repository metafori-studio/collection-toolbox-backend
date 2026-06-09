<?php

namespace Metafori\Etno\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Metafori\Etno\Database\Factories\ItemOriginatorFactory;
use Metafori\Etno\Models\Concerns\Originator;

class ItemOriginator extends Model
{
    use Originator;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ItemOriginatorFactory
    {
        return ItemOriginatorFactory::new();
    }

    protected $table = 'etno_item_originators';

    protected $touches = ['item'];

    protected $guarded = [];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
