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
            'filter' => ['array'],

            /**
             * Filter by Item types (matches any if multiple given).
             */
            'filter.type' => ['array'],
            'filter.type.*' => ['string'],

            /**
             * Filter by languages (matches any if multiple given).
             */
            'filter.language' => ['array'],
            'filter.language.*' => ['string'],

            /**
             * Filter by accrual methods (matches any if multiple given).
             */
            'filter.accrual_method' => ['array'],
            'filter.accrual_method.*' => ['string'],

            /**
             * Filter by collection methods (matches any if multiple given).
             */
            'filter.collection_method' => ['array'],
            'filter.collection_method.*' => ['string'],

            /**
             * Filter by access rights (matches any if multiple given).
             */
            'filter.access_rights' => ['array'],
            'filter.access_rights.*' => ['string'],

            /**
             * Filter by licenses (matches any if multiple given).
             */
            'filter.license' => ['array'],
            'filter.license.*' => ['string'],

            /**
             * Filter by production methods (matches any if multiple given).
             */
            'filter.production_methods' => ['array'],
            'filter.production_methods.*' => ['string'],

            /**
             * Filter by the IDs of related authors (matches any if multiple given).
             */
            'filter.author.person_id' => ['array'],
            'filter.author.person_id.*' => ['integer'],

            /**
             * Filter by the IDs of related researchers.
             */
            'filter.researcher.person_id' => ['array'],
            'filter.researcher.person_id.*' => ['integer'],

            /**
             * Filter by the IDs of related originators.
             */
            'filter.originator.person_id' => ['array'],
            'filter.originator.person_id.*' => ['integer'],

            /**
             * Filter by the IDs of mapped keywords.
             */
            'filter.keyword.id' => ['array'],
            'filter.keyword.id.*' => ['integer'],

            /**
             * Filter by the IDs of related research collections.
             */
            'filter.research_collection.id' => ['array'],
            'filter.research_collection.id.*' => ['integer'],

            /**
             * Filter by the ID of the related institution.
             */
            'filter.institution.id' => ['array'],
            'filter.institution.id.*' => ['integer'],

            /**
             * Filter by the ID of the related project.
             */
            'filter.project.id' => ['array'],
            'filter.project.id.*' => ['integer'],

            /**
             * Filter by the mapped locality (format is `{type}:{id}`). Matches any if multiple given.
             */
            'filter.locality' => ['array'],
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
