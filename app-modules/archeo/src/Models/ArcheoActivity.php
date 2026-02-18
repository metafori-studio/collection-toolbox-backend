<?php

namespace Metafori\Archeo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Metafori\Core\Models\Activity;

class ArcheoActivity extends Model
{
    protected $table = 'archeo_activity_details';

    protected $fillable = [
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
    ];

    public function activity(): MorphOne
    {
        return $this->morphOne(Activity::class, 'details');
    }
}
