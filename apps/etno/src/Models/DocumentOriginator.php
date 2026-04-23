<?php

namespace Metafori\Etno\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Metafori\Etno\Models\Concerns\Originator;

class DocumentOriginator extends Model
{
    use Originator;

    protected $table = 'etno_document_originators';

    protected $touches = ['document'];

    protected $guarded = [];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
