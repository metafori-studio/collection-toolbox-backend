<?php

namespace Metafori\Etno\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ItemAggregationsRequest extends FormRequest
{
    use Concerns\HasFilterRules;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return $this->filterRules;
    }
}
