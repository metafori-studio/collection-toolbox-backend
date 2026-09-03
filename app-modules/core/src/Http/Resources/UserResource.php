<?php

namespace Metafori\Core\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Metafori\Core\Models\User;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            /* @var int */
            'id' => $this->id,
            /* @var string */
            'email' => $this->email,
            /* @var string|null */
            'preferred_locale' => $this->preferred_locale,
        ];
    }
}
