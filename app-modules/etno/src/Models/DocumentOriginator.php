<?php

namespace Metafori\Etno\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Metafori\Etno\Database\Factories\DocumentOriginatorFactory;
use Metafori\Etno\Models\Concerns\Originator;

class DocumentOriginator extends Model
{
    use Originator;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): DocumentOriginatorFactory
    {
        return DocumentOriginatorFactory::new();
    }

    protected $table = 'etno_document_originators';

    protected $touches = ['document'];

    protected $guarded = [];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }
}
