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

        return $rules;
    }
}
