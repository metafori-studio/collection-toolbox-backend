<?php

return [
    'navigation_groups' => [
        'system' => 'System',
    ],
    'resources' => [
        'location' => [
            'label' => 'Location',
            'plural_label' => 'Locations',
            'navigation_label' => 'Locations',
        ],
        'keyword' => [
            'label' => 'Keyword',
            'plural_label' => 'Keywords',
            'navigation_label' => 'Keywords',
        ],
        'organization' => [
            'label' => 'Organization',
            'plural_label' => 'Organizations',
            'navigation_label' => 'Organizations',
        ],
        'person' => [
            'label' => 'Person',
            'plural_label' => 'People',
            'navigation_label' => 'People',
        ],
        'user' => [
            'label' => 'User',
            'plural_label' => 'Users',
            'navigation_label' => 'Users',
        ],
    ],
    'fields' => [
        'name' => 'Name',
        'user_name' => 'Name',
        'email' => 'Email',
        'preferred_locale' => 'Preferred locale',
        'roles' => 'Roles',
        'precision' => 'Precision',
        'start_precision' => 'Start precision',
        'end_precision' => 'End precision',
        'date_range' => 'Date range',
        'start' => 'Start',
        'date' => 'Date',
        'end' => 'End',
        'country' => 'Country',
        'region' => 'Region',
        'district' => 'District',
        'municipality' => 'Municipality',
        'parent' => 'Parent',
        'latitude' => 'Latitude',
        'longitude' => 'Longitude',
        'given_name' => 'Given Name',
        'family_name' => 'Family Name',
        'orcid' => 'ORCID',
        'ror_id' => 'ROR ID',
    ],
    'placeholders' => [
        'none' => 'None',
    ],
    'validation' => [
        'end_date_must_be_after_start_date' => 'The end date must be after the start date.',
    ],
];
