<?php

namespace Metafori\Art\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CollectionIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function filters(): array
    {
        $filters = $this->validated('filter', []);

        if (\array_key_exists('is_featured', $filters)) {
            $filters['is_featured'] = $this->boolean('filter.is_featured');
        }

        return $filters;
    }

    public function perPage(): int
    {
        return (int) ($this->validated('per_page') ?? 15);
    }

    /**
     * @return array<string, 'asc'|'desc'>
     */
    public function sorts(): array
    {
        $sort = $this->validated('sort', '-published_at') ?? '-published_at';
        $direction = \str_starts_with($sort, '-') ? 'desc' : 'asc';
        $field = \ltrim($sort, '-');

        return [$field => $direction];
    }

    public function rules(): array
    {
        return [
            'filter' => ['sometimes', 'array'],
            'filter.is_featured' => ['sometimes', 'boolean'],
            'sort' => ['nullable', Rule::in(['published_at', '-published_at', 'title', '-title', 'artworks_count', '-artworks_count'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
