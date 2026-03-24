<?php

namespace Metafori\Etno\Models\Concerns;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Metafori\Core\Models\Person;
use Spatie\Translatable\HasTranslations;

trait Originator
{
    use HasFactory, HasTranslations;

    protected array $translatable = ['label'];

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
