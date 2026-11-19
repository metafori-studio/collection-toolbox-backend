<?php

namespace Metafori\Art\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CollectionArtworksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, 'asc'|'desc'>
     */
    public function sorts(): array
    {
        $sort = $this->validated('sort');

        if (! $sort) {
            return [];
        }

        $direction = \str_starts_with($sort, '-') ? 'desc' : 'asc';
        $field = \ltrim($sort, '-');

        return [$field => $direction];
    }

    public function perPage(): int
    {
        return (int) ($this->validated('per_page') ?? 15);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'sort' => ['nullable', Rule::in(['title', '-title'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
