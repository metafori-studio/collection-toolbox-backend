<?php

namespace Metafori\Core\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Metafori\Core\Models\Person;

/**
 * @mixin Person
 */
class PersonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'given_name' => $this->given_name,
            'family_name' => $this->family_name,
            'display_name' => $this->display_name,
            'orcid' => $this->orcid,
        ];
    }
}
