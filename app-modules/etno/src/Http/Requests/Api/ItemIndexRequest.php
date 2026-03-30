<?php

namespace Metafori\Etno\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ItemIndexRequest extends FormRequest
{
    use Concerns\HasFilterRules;

    public function authorize(): bool
    {
        return true;
    }

    public function sorts(): array
    {
        $sortString = $this->validated('sort');

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
            ...$this->filterRules(),

            /**
             * Specific model property mapped dynamically to a scalar. Supports comma separation for multiple sort properties. To sort descending, prepend a hyphen (`-`) to the target property name.
             *
             * @example -type
             */
            'sort' => ['nullable', 'string'],

            /**
             * Page number for pagination.
             */
            'page' => ['nullable', 'integer', 'min:1'],

            /**
             * Number of items per page.
             */
            'per_page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
