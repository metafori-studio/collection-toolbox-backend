<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Database Table Name
    |--------------------------------------------------------------------------
    |
    | The name of the table used to store archeo activities.
    |
    */
    'table_name' => 'archeo_activities',

    /**
     * The name of the table used to store galleries.
     */
    'galleries_table_name' => 'archeo_galleries',

    /**
     * The name of the table used to store user-activity assignments.
     */
    'assignments_table_name' => 'archeo_activity_assignments',

    /*
    |--------------------------------------------------------------------------
    | Media Collections
    |--------------------------------------------------------------------------
    |
    | Names of media collections used by the activity model.
    |
    */
    'media_disk' => env('ARCHEO_MEDIA_DISK', 'local'),
    'media_collections' => [
        'attachments' => 'activity_attachments',
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Settings
    |--------------------------------------------------------------------------
    |
    | The name of the queue used for importing activities.
    |
    */
    'import_queue' => env('ARCHEO_IMPORT_QUEUE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Excel Import Mapping
    |--------------------------------------------------------------------------
    |
    | Maps Excel column letters to Activity model attributes.
    |
    */
    'import_mapping' => [
        'activity_number' => 'A',
        'cvs_number' => 'B',
        'registration_year' => 'C',
        'years' => 'D',
        'activity_type' => 'E',
        'cadastral_area' => 'F',
        'municipality' => 'G',
        'position' => 'H',
        'district' => 'I',
        'research_leader' => 'J',
        'author_ns' => 'K',
        'institution' => 'L',
        'action_number' => 'M',
        'dating_ns' => 'N',
        'dating_ceans' => 'O',
        'site_type_original' => 'P',
        'dating_site_type' => 'Q',
        'localization_degree' => 'R',
        'has_gis_link' => 'S',
        'coordinate_x' => 'T',
        'coordinate_y' => 'U',
        'size_category' => 'V',
    ],
];
