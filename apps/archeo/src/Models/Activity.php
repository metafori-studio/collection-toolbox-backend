<?php

namespace Metafori\Archeo\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Metafori\Archeo\Services\CoordinateTransformer;
use Metafori\Core\Models\User;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Activity extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public const string MAP_POINTS_CACHE_KEY = 'archeo.activity.map-points';

    protected $table = 'archeo_activities';

    protected static function boot(): void
    {
        parent::boot();

        static::updating(function (Activity $activity) {
            if ($activity->isDirty('activity_number')) {
                throw new \RuntimeException('The activity number is immutable and cannot be changed.');
            }
        });
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
        'coordinate_x',
        'coordinate_y',
        'latitude',
        'longitude',
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
        'import_id',
    ];

    protected $casts = [
        'dating_ns' => 'array',
        'dating_ceans' => 'array',
        'dating_site_type' => 'array',
        'author_ns' => 'array',
        'has_gis_link' => 'boolean',
        'coordinate_x' => 'float',
        'coordinate_y' => 'float',
    ];

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class);
    }

    public function import(): BelongsTo
    {
        return $this->belongsTo(ActivityImport::class, 'import_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ActivityAssignment::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('pdfs')
            ->useDisk(config('archeo.pdfs_disk', 'public'))
            ->acceptsMimeTypes(['application/pdf']);
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
     * Get GCS (WGS84) Latitude and Longitude translated from S-JTSK.
     */
    protected function gcsCoordinates(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->coordinate_x || ! $this->coordinate_y) {
                return null;
            }

            return app(CoordinateTransformer::class)->sjtskToWgs84(
                (float) $this->coordinate_x,
                (float) $this->coordinate_y
            );
        });
    }
}
