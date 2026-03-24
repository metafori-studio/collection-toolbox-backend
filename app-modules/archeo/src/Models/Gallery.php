<?php

namespace Metafori\Archeo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Gallery extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected const DEFAULT_TABLE_NAME = 'archeo_galleries';

    public const DEFAULT_MEDIA_DISK = 'local';

    public function getTable(): string
    {
        return config('archeo.galleries_table_name', self::DEFAULT_TABLE_NAME);
    }

    protected $fillable = [
        'activity_id',
        'title',
        'description',
        'sort_order',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('gallery_images')
            ->useDisk(config('archeo.media_disk', self::DEFAULT_MEDIA_DISK))
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Contain, 300, 300)
            ->nonQueued();
    }
}
