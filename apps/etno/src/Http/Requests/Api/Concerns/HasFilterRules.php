<?php

namespace Metafori\Etno\Http\Requests\Api\Concerns;

use Metafori\Etno\Support\FacetMetadata;

trait HasFilterRules
{
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
            $rules["filter.{$escapedField}"] = ['array', 'list'];
            $rules["filter.{$escapedField}.*"] = ['integer'];
        }

        $rules['filter.time_period_from'] = ['nullable', 'date_format:Y'];
        $rules['filter.time_period_to'] = ['nullable', 'date_format:Y', function ($attribute, $value, $fail) {
            if ($this->filled('filter.time_period_from') && $value < $this->filter['time_period_from']) {
                $fail("The {$attribute} must be greater than or equal to filter.time_period_from.");
            }
        }];

        return $rules;
    }
}
