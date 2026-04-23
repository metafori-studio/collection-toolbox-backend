<?php

namespace Metafori\Archeo\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ActivityIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function sorts(): array
    {
        $sortString = $this->query('sort');

        if (! $sortString) {
            return [];
        }

        return str($sortString)
            ->explode(',')
            ->filter()
            ->mapWithKeys(function ($sort) {
                $dir = str_starts_with($sort, '-') ? 'desc' : 'asc';
                $field = ltrim($sort, '-');

                return [$field => $dir];
            })
            ->toArray();
    }

    public function rules(): array
    {
        return [
            /**
             * Filter activities by field values. Each key is a filterable field and the value is an array of accepted values.
             *
             * Filterable fields: activity_type, activity_number, activity_year_start, activity_year_end, municipality, district, cadastral_area, institution, research_leader, registration_year, size_category, author_ns, dating_ns, dating_ceans, dating_site_type.
             *
             * @example {"activity_type":["surface_survey"],"district":["Brno-venkov"]}
             */
            'filter' => ['nullable', 'array'],
            'filter.*' => ['nullable', 'array'],

            /**
             * Sort activities by a specific model property. Supports comma separation for multiple sort properties. To sort descending, prepend a hyphen (`-`) to the target property name.
             *
             * Sortable fields: created_at, activity_year_start, activity_year_end, activity_type, activity_number, municipality, district, cadastral_area, institution, research_leader, registration_year, size_category.
             *
             * @example -activity_year_start,municipality
             */
            'sort' => ['nullable', 'string'],

            /**
             * Page number for pagination.
             */
            'page' => ['nullable', 'integer', 'min:1'],

            /**
             * Number of items per page.
             */
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
