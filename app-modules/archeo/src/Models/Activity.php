<?php

namespace Metafori\Archeo\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Activity extends Model implements HasMedia
{
    use InteractsWithMedia;

    public function getTable(): string
    {
        return config('archeo.table_name', 'archeo_activities');
    }

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
        'wgs84_coordinate_x',
        'wgs84_coordinate_y',
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
        'file_name',
    ];

    protected $casts = [
        'dating_ns' => 'array',
        'dating_ceans' => 'array',
        'dating_site_type' => 'array',
        'has_gis_link' => 'boolean',
        'wgs84_coordinate_x' => 'decimal:6',
        'wgs84_coordinate_y' => 'decimal:6',
    ];

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class);
    }

    /**
     * Get the formatted coordinates (Lat, Long).
     */
    protected function coordinates(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->wgs84_coordinate_x === null || $this->wgs84_coordinate_y === null) {
                    return null;
                }

                return "WGS84: {$this->wgs84_coordinate_x}, {$this->wgs84_coordinate_y}";
            }
        );
    }

    public function registerMediaCollections(): void
    {
        // No default attachments collection anymore, handled via Galleries relation
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();
    }
}
