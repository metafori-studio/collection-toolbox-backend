<?php

namespace Metafori\Etno\Http\Requests\Api;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class ItemIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            /**
             * Specific model property mapped dynamically to a scalar. Supports comma separation for multiple sort properties. To sort descending, prepend a hyphen (`-`) to the target property name.
             *
             * @example -time_period_start
             */
            'sort' => ['nullable', 'string'],

            /**
             * Map of filtering constraints.
             */
            'filter' => ['nullable', 'array'],

            /**
             * Filter by Item types (matches any if multiple given).
             */
            'filter.type' => ['nullable', 'array'],
            'filter.type.*' => ['string'],

            /**
             * Filter by languages (matches any if multiple given).
             */
            'filter.language' => ['nullable', 'array'],
            'filter.language.*' => ['string'],

            /**
             * Filter by accrual methods (matches any if multiple given).
             */
            'filter.accrual_method' => ['nullable', 'array'],
            'filter.accrual_method.*' => ['string'],

            /**
             * Filter by collection methods (matches any if multiple given).
             */
            'filter.collection_method' => ['nullable', 'array'],
            'filter.collection_method.*' => ['string'],

            /**
             * Filter by access rights (matches any if multiple given).
             */
            'filter.access_rights' => ['nullable', 'array'],
            'filter.access_rights.*' => ['string'],

            /**
             * Filter by licenses (matches any if multiple given).
             */
            'filter.license' => ['nullable', 'array'],
            'filter.license.*' => ['string'],

            /**
             * Filter by production methods (matches any if multiple given).
             */
            'filter.production_methods' => ['nullable', 'array'],
            'filter.production_methods.*' => ['string'],

            /**
             * Filter by the IDs of related authors (matches any if multiple given).
             */
            'filter.author.person_id' => ['nullable', 'array'],
            'filter.author.person_id.*' => ['integer'],

            /**
             * Filter by the IDs of related researchers.
             */
            'filter.researcher.person_id' => ['nullable', 'array'],
            'filter.researcher.person_id.*' => ['integer'],

            /**
             * Filter by the IDs of related originators.
             */
            'filter.originator.person_id' => ['nullable', 'array'],
            'filter.originator.person_id.*' => ['integer'],

            /**
             * Filter by the IDs of mapped keywords.
             */
            'filter.keyword.id' => ['nullable', 'array'],
            'filter.keyword.id.*' => ['integer'],

            /**
             * Filter by the IDs of related research collections.
             */
            'filter.research_collection.id' => ['nullable', 'array'],
            'filter.research_collection.id.*' => ['integer'],

            /**
             * Filter by the ID of the related institution.
             */
            'filter.institution.id' => ['nullable', 'array'],
            'filter.institution.id.*' => ['integer'],

            /**
             * Filter by the ID of the related project.
             */
            'filter.project.id' => ['nullable', 'array'],
            'filter.project.id.*' => ['integer'],

            /**
             * Filter by the mapped locality (format is `{type}:{id}`). Matches any if multiple given.
             */
            'filter.locality' => ['nullable', 'array'],
            'filter.locality.*' => ['string'],

            /**
             * Filter for items starting on or after a specified date (`Y-m-d`).
             */
            'filter.time_period_start' => ['nullable', 'date'],

            /**
             * Filter for items ending on or before a specified date (`Y-m-d`).
             */
            'filter.time_period_end' => ['nullable', 'date'],
        ];
    }

    protected function failedValidation(Validator $validator): void {}
}
