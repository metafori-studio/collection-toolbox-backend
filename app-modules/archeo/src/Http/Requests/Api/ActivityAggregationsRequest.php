<?php

namespace Metafori\Archeo\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ActivityAggregationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /**
             * Filter activities by field values. Each key is a filterable field and the value is an array of accepted values.
             * When aggregating, the filter for a given field is excluded from its own bucket query so all values remain visible.
             *
             * Aggregatable fields: activity_type, activity_year_start, activity_year_end, municipality, district, cadastral_area, institution, research_leader, size_category, registration_year, author_ns, dating_ns, dating_ceans, dating_site_type.
             *
             * @example {"municipality":["Brno"],"activity_type":["surface_survey"]}
             */
            'filter' => ['nullable', 'array'],
            'filter.*' => ['nullable', 'array'],
            'filter.*.*' => ['string'],
        ];
    }
}
