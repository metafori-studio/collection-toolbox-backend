<?php

namespace Metafori\Archeo\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Metafori\Archeo\Services\CoordinateTransformer;
use Metafori\Core\Models\User;
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

    public function getRouteKeyName(): string
    {
        return 'activity_number';
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
        'wgs84_coordinate_x' => 'float',
        'wgs84_coordinate_y' => 'float',
    ];

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ActivityAssignment::class);
    }

    /**
     * Check if the activity is assigned to a specific user and access is not expired.
     */
    public function isAssignedTo(User $user): bool
    {
        return $this->assignments()
            ->where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Get GCS (WGS84) Latitude translated from S-JTSK.
     */
    protected function gcsLatitude(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->wgs84_coordinate_x || ! $this->wgs84_coordinate_y) {
                return null;
            }

            return app(CoordinateTransformer::class)->sjtskToWgs84(
                (float) $this->wgs84_coordinate_x,
                (float) $this->wgs84_coordinate_y
            )['latitude'] ?? null;
        });
    }

    /**
     * Get GCS (WGS84) Longitude translated from S-JTSK.
     */
    protected function gcsLongitude(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->wgs84_coordinate_x || ! $this->wgs84_coordinate_y) {
                return null;
            }

            return app(CoordinateTransformer::class)->sjtskToWgs84(
                (float) $this->wgs84_coordinate_x,
                (float) $this->wgs84_coordinate_y
            )['longitude'] ?? null;
        });
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
