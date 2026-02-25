<?php

namespace Metafori\Core\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Person extends Model
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
            fn () => Str::trim("{$this->given_name} {$this->family_name}")
        );
    }
}
