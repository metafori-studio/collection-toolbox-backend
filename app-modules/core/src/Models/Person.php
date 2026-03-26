<?php

namespace Metafori\Core\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Stringable;

class Person extends Model implements Stringable
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'given_name',
        'family_name',
        'orcid',
    ];

    public function displayName(): Attribute
    {
        return Attribute::get(
            fn () => Str::trim("{$this->family_name}, {$this->given_name}")
        );
    }

    public function __toString(): string
    {
        return $this->display_name;
    }
}
