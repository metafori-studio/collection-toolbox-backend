<?php

namespace Metafori\Art\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Metafori\Art\Http\Requests\Api\Concerns\HasFilterRules;

class ArtworkIndexRequest extends FormRequest
{
    use HasFilterRules;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, 'asc'|'desc'>
     */
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

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            ...$this->filterRules(),

            /**
             * Specific model property mapped dynamically to a scalar. Supports comma separation for multiple sort properties. To sort descending, prepend a hyphen (`-`) to the target property name.
             *
             * @example -id
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
