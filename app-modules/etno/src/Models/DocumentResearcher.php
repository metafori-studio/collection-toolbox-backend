<?php

namespace Metafori\Etno\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Metafori\Core\Models\Person;

class DocumentResearcher extends Model
{
    protected $table = 'etno_document_researchers';

    protected $guarded = [];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
