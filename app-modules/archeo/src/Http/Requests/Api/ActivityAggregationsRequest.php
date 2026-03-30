<?php

namespace Metafori\Archeo\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ActivityAggregationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'filter' => ['nullable', 'array'],
            'filter.*' => ['nullable', 'array'],
        ];
    }
}
