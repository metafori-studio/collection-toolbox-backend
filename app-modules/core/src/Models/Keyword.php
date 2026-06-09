<?php

namespace Metafori\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Metafori\Core\Database\Factories\KeywordFactory;
use Spatie\Translatable\HasTranslations;
use Stringable;

class Keyword extends Model implements Stringable
{
    use HasFactory, HasTranslations, SoftDeletes;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): KeywordFactory
    {
        return KeywordFactory::new();
    }

    protected $fillable = [
        'name',
    ];

    public array $translatable = ['name'];

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
