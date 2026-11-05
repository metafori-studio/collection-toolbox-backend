<?php

namespace Metafori\Etno\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Metafori\Etno\Models\Document;
use Metafori\Etno\Support\ItemSearchSynchronizer;

class DocumentPivot extends Pivot
{
    protected static function booted(): void
    {
        $sync = function (DocumentPivot $pivot): void {
            if ($pivot->document !== null) {
                app(ItemSearchSynchronizer::class)->syncDocumentItems($pivot->document);
            }
        };

        static::saved($sync);
        static::deleted($sync);
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
