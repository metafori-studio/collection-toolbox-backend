<?php

namespace Metafori\Art\Http\Requests\Api\Concerns;

use Metafori\Art\Support\FacetMetadata;

trait HasFilterRules
{
    /**
     * @return array<string, mixed>
     */
    public function filterRules(): array
    {
        $rules = [];

        foreach (FacetMetadata::enums() as $field) {
            $escapedField = str_replace('.', '\.', $field);
            $rules["filter.{$escapedField}"] = ['array', 'list'];
            $rules["filter.{$escapedField}.*"] = ['string'];
        }

        foreach (FacetMetadata::models() as $field) {
            $escapedField = str_replace('.', '\.', $field);
            $model = FacetMetadata::MODEL_MAPPING[$field];
            $keyType = (new $model)->getKeyType();

            $rules["filter.{$escapedField}"] = ['array', 'list'];
            $rules["filter.{$escapedField}.*"] = match ($keyType) {
                'string' => ['string'],
                default => ['integer'],
            };
        }

        $rules['filter.author\.label'] = ['array', 'list'];
        $rules['filter.author\.label.*'] = ['string'];

        $rules['filter.creation_date_from'] = ['nullable', 'date_format:Y'];
        $rules['filter.creation_date_to'] = ['nullable', 'date_format:Y', function ($attribute, $value, $fail) {
            if ($this->filled('filter.creation_date_from') && $value < $this->input('filter.creation_date_from')) {
                $fail("The {$attribute} must be greater than or equal to filter.creation_date_from.");
            }
        }];

        return $rules;
    }
}
