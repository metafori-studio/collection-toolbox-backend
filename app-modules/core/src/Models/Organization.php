<?php

namespace Metafori\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;
use Stringable;

class Organization extends Model implements Stringable
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = [
        'name',
        'ror_id',
    ];

    public array $translatable = ['name'];

    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
