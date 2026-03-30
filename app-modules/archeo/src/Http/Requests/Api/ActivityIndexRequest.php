<?php

namespace Metafori\Archeo\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ActivityIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function sorts(): array
    {
        $sortString = $this->query('sort');

        if (! $sortString) {
            return [];
        }

        return str($sortString)
            ->explode(',')
            ->filter()
            ->mapWithKeys(function ($sort) {
                $dir = str_starts_with($sort, '-') ? 'desc' : 'asc';
                $field = ltrim($sort, '-');

                return [$field => $dir];
            })
            ->toArray();
    }

    public function rules(): array
    {
        return [
            'filter' => ['nullable', 'array'],
            'filter.*' => ['nullable', 'array'],
            'sort' => ['nullable', 'string'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
