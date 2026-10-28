<?php

namespace Metafori\Art\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ArtworkSearchRequest extends FormRequest
{
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
            /**
             * The query string to search for.
             */
            'q' => ['required', 'string'],

            /**
             * The number of items to return.
             */
            'size' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
