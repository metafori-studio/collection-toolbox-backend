<?php

namespace Metafori\Art\Http\Resources;

use Illuminate\Http\Resources\JsonApi\JsonApiResource;
use Metafori\Core\Models\Person;

/**
 * @mixin Person
 */
class PersonResource extends JsonApiResource
{
    /**
     * @var array<string>
     */
    public $attributes = [
        'given_name',
        'family_name',
        'display_name',
        'orcid',
    ];
}
