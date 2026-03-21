<?php

return [
    'sections' => [
        'general' => 'General Information',
        'location' => 'Location Details',
        'research' => 'Research & Dating',
        'attachments' => 'Attachments',
        'gallery' => 'Gallery',
    ],
    'fields' => [
        'activity_number' => 'Activity Number',
        'activity_type' => 'Activity Type',
        'cvs_number' => 'ČVS Number',
        'cvs_number_short' => 'ČVS',
        'activity_year_start' => 'Year Start',
        'activity_year_end' => 'Year End',
        'year_start_short' => 'Year Start',
        'year_end_short' => 'Year End',
        'registration_year' => 'Registration Year',
        'action_number' => 'Action Number',
        'file_name' => 'File Name',
        'municipality' => 'Municipality',
        'cadastral_area' => 'Cadastral Area',
        'district' => 'District',
        'position' => 'Position',
        'localization_degree' => 'Localization Degree',
        'coordinate_x' => 'WGS84 Latitude (X)',
        'coordinate_y' => 'WGS84 Longitude (Y)',
        'has_gis_link' => 'Has GIS Link',
        'gis_short' => 'GIS',
        'research_leader' => 'Research Leader',
        'institution' => 'Institution',
        'author_ns' => 'Author - NS',
        'dating_ns' => 'Dating - NS',
        'dating_ceans' => 'Dating - CEANS',
        'dating_site_type' => 'Dating - Site Type',
        'site_type_original' => 'Site Type (Original)',
        'size_category' => 'Size Category',
        'gallery' => 'Gallery',
    ],
    'actions' => [
        'import_excel' => [
            'label' => 'Import Excel',
            'fields' => [
                'file' => 'Excel File',
            ],
        ],
    ],
    'notifications' => [
        'import_queued' => [
            'title' => 'Import Queued',
            'body' => 'The import process has started in the background. you will be notified when it is complete.',
        ],
        'import_success' => [
            'title' => 'Import Successful',
            'body' => 'Successfully imported :count activities.',
        ],
        'import_failed' => [
            'title' => 'Import Failed',
        ],
    ],
    'import' => [
        'row_error' => 'Row :row: :message',
        'errors' => [
            'missing_activity_number' => 'Missing activity number.',
            'invalid_activity_number' => 'The activity number does not contain any digits.',
            'invalid_year' => 'Invalid year format ":value". Expected "YYYY" or "YYYY-YYYY".',
            'year_required' => 'Activity year is required.',
        ],
    ],
];
