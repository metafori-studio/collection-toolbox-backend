<?php

namespace Metafori\Art\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Metafori\Art\Http\Requests\Api\Concerns\HasFilterRules;

class ArtworkAggregationsRequest extends FormRequest
{
    use HasFilterRules;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->filterRules(),
        ];
    }
}
