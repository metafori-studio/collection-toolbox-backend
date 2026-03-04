<?php

namespace Metafori\Archeo\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $table = 'archeo_activities';

    protected $fillable = [
        'activity_number',
        'activity_year_start',
        'activity_year_end',
        'activity_type',
        'action_number',
        'registration_year',
        'cadastral_area',
        'municipality',
        'position',
        'district',
        'localization_degree',
        'coordinate_x',
        'coordinate_y',
        'has_gis_link',
        'cvs_number',
        'research_leader',
        'author_ns',
        'institution',
        'dating_ns',
        'dating_ceans',
        'dating_site_type',
        'site_type_original',
        'size_category',
    ];

    protected $casts = [
        'dating_ns' => 'array',
        'dating_ceans' => 'array',
        'dating_site_type' => 'array',
        'site_type_original' => 'array',
        'has_gis_link' => 'boolean',
    ];
}
