<?php

namespace Metafori\Etno\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
             * @example -type
             */
            'sort' => ['nullable', 'string'],

            /**
             * Map of filtering constraints.
             */
            'filter' => [
                Rule::array([
                    'type',
                    'language',
                    'accrual_method',
                    'collection_method',
                    'access_rights',
                    'license',
                    'production_methods',
                    'author',
                    'researcher',
                    'originator',
                    'keyword',
                    'research_collection',
                    'institution',
                    'project',
                    'country',
                    'region',
                    'district',
                    'municipality',
                    'municipality_part',
                    'location',
                ]),
            ],
            'filter.type' => ['array', 'list'],
            /**
             * Filter by Item types (matches any if multiple given).
             */
            'filter.type.*' => ['string'],
            'filter.language' => ['array', 'list'],
            /**
             * Filter by languages (matches any if multiple given).
             */
            'filter.language.*' => ['string'],
            'filter.accrual_method' => ['array', 'list'],
            /**
             * Filter by accrual methods (matches any if multiple given).
             */
            'filter.accrual_method.*' => ['string'],
            'filter.collection_method' => ['array', 'list'],
            /**
             * Filter by collection methods (matches any if multiple given).
             */
            'filter.collection_method.*' => ['string'],
            'filter.access_rights' => ['array', 'list'],
            /**
             * Filter by access rights (matches any if multiple given).
             */
            'filter.access_rights.*' => ['string'],
            'filter.license' => ['array', 'list'],
            /**
             * Filter by licenses (matches any if multiple given).
             */
            'filter.license.*' => ['string'],
            'filter.production_methods' => ['array', 'list'],
            /**
             * Filter by production methods (matches any if multiple given).
             */
            'filter.production_methods.*' => ['string'],
            'filter.author' => ['array:person_id'],
            'filter.author.person_id' => ['array', 'list'],
            /**
             * Filter by the IDs of related authors (matches any if multiple given).
             */
            'filter.author.person_id.*' => ['integer'],
            'filter.researcher' => ['array:person_id'],
            'filter.researcher.person_id' => ['array', 'list'],
            /**
             * Filter by the IDs of related researchers.
             */
            'filter.researcher.person_id.*' => ['integer'],
            'filter.originator' => ['array:person_id'],
            'filter.originator.person_id' => ['array', 'list'],
            /**
             * Filter by the IDs of related originators.
             */
            'filter.originator.person_id.*' => ['integer'],
            'filter.keyword' => ['array:id'],
            'filter.keyword.id' => ['array', 'list'],
            /**
             * Filter by the IDs of mapped keywords.
             */
            'filter.keyword.id.*' => ['integer'],
            'filter.research_collection' => ['array:id'],
            'filter.research_collection.id' => ['array', 'list'],
            /**
             * Filter by the IDs of related research collections.
             */
            'filter.research_collection.id.*' => ['integer'],
            'filter.institution' => ['array:id'],
            'filter.institution.id' => ['array', 'list'],
            /**
             * Filter by the ID of the related institution.
             */
            'filter.institution.id.*' => ['integer'],
            'filter.project' => ['array:id'],
            'filter.project.id' => ['array', 'list'],
            /**
             * Filter by the ID of the related project.
             */
            'filter.project.id.*' => ['integer'],
            'filter.country' => ['array:id'],
            'filter.country.id' => ['array', 'list'],
            /**
             * Filter by mapped locality. Specify multiple values to match any.
             */
            'filter.country.id.*' => ['integer'],
            'filter.region' => ['array:id'],
            'filter.region.id' => ['array', 'list'],
            /**
             * Filter by mapped locality. Specify multiple values to match any.
             */
            'filter.region.id.*' => ['integer'],
            'filter.district' => ['array:id'],
            'filter.district.id' => ['array', 'list'],
            /**
             * Filter by mapped locality. Specify multiple values to match any.
             */
            'filter.district.id.*' => ['integer'],
            'filter.municipality' => ['array:id'],
            'filter.municipality.id' => ['array', 'list'],
            /**
             * Filter by mapped locality. Specify multiple values to match any.
             */
            'filter.municipality.id.*' => ['integer'],
            'filter.municipality_part' => ['array:id'],
            'filter.municipality_part.id' => ['array', 'list'],
            /**
             * Filter by mapped locality. Specify multiple values to match any.
             */
            'filter.municipality_part.id.*' => ['integer'],
            'filter.location' => ['array:id'],
            'filter.location.id' => ['array', 'list'],
            /**
             * Filter by mapped locality. Specify multiple values to match any.
             */
            'filter.location.id.*' => ['integer'],

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
