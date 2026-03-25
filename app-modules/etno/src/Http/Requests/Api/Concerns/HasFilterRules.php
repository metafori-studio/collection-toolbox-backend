<?php

namespace Metafori\Etno\Http\Requests\Api\Concerns;

trait HasFilterRules
{
    protected $filterRules = [
        /**
         * Map of filtering constraints.
         */
        'filter' => ['array'],
        'filter.type' => ['array'],
        /**
         * Filter by Item types (matches any if multiple given).
         */
        'filter.type.*' => ['string'],
        'filter.language' => ['array'],
        /**
         * Filter by languages (matches any if multiple given).
         */
        'filter.language.*' => ['string'],
        'filter.accrual_method' => ['array'],
        /**
         * Filter by accrual methods (matches any if multiple given).
         */
        'filter.accrual_method.*' => ['string'],
        'filter.collection_method' => ['array'],
        /**
         * Filter by collection methods (matches any if multiple given).
         */
        'filter.collection_method.*' => ['string'],
        'filter.access_rights' => ['array'],
        /**
         * Filter by access rights (matches any if multiple given).
         */
        'filter.access_rights.*' => ['string'],
        'filter.license' => ['array'],
        /**
         * Filter by licenses (matches any if multiple given).
         */
        'filter.license.*' => ['string'],
        'filter.production_methods' => ['array'],
        /**
         * Filter by production methods (matches any if multiple given).
         */
        'filter.production_methods.*' => ['string'],
        'filter.author\.person_id' => ['array'],
        /**
         * Filter by the IDs of related authors (matches any if multiple given).
         */
        'filter.author\.person_id.*' => ['integer'],
        'filter.researcher\.person_id' => ['array'],
        /**
         * Filter by the IDs of related researchers.
         */
        'filter.researcher\.person_id.*' => ['integer'],
        'filter.originator\.person_id' => ['array'],
        /**
         * Filter by the IDs of related originators.
         */
        'filter.originator\.person_id.*' => ['integer'],
        'filter.keyword\.id' => ['array'],
        /**
         * Filter by the IDs of mapped keywords.
         */
        'filter.keyword\.id.*' => ['integer'],
        'filter.research_collection\.id' => ['array'],
        /**
         * Filter by the IDs of related research collections.
         */
        'filter.research_collection\.id.*' => ['integer'],
        'filter.institution\.id' => ['array'],
        /**
         * Filter by the ID of the related institution.
         */
        'filter.institution\.id.*' => ['integer'],
        'filter.project\.id' => ['array'],
        /**
         * Filter by the ID of the related project.
         */
        'filter.project\.id.*' => ['integer'],
        'filter.country\.id' => ['array'],
        /**
         * Filter by mapped locality. Specify multiple values to match any.
         */
        'filter.country\.id.*' => ['integer'],
        'filter.region\.id' => ['array'],
        /**
         * Filter by mapped locality. Specify multiple values to match any.
         */
        'filter.region\.id.*' => ['integer'],
        'filter.district\.id' => ['array'],
        /**
         * Filter by mapped locality. Specify multiple values to match any.
         */
        'filter.district\.id.*' => ['integer'],
        'filter.municipality\.id' => ['array'],
        /**
         * Filter by mapped locality. Specify multiple values to match any.
         */
        'filter.municipality\.id.*' => ['integer'],
        'filter.municipality_part\.id' => ['array'],
        /**
         * Filter by mapped locality. Specify multiple values to match any.
         */
        'filter.municipality_part\.id.*' => ['integer'],
        'filter.location\.id' => ['array'],
        /**
         * Filter by mapped locality. Specify multiple values to match any.
         */
        'filter.location\.id.*' => ['integer'],
    ];
}
