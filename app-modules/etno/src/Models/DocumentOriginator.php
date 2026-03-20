<?php

namespace Metafori\Etno\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Metafori\Core\Models\Person;
use Spatie\Translatable\HasTranslations;

class DocumentOriginator extends Model
{
    use HasTranslations;

    protected $table = 'etno_document_originators';

    protected $guarded = [];

    protected array $translatable = ['label'];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
