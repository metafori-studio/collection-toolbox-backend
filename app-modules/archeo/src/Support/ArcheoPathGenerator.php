<?php

namespace Metafori\Archeo\Support;

use Metafori\Archeo\Models\Activity;
use Metafori\Archeo\Models\Gallery;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;

class ArcheoPathGenerator implements PathGenerator
{
    /*
     * Get the path for the given media, relative to the root storage path.
     */
    public function getPath(Media $media): string
    {
        return $this->getBasePath($media).'/';
    }

    /*
     * Get the path for conversions of the given media, relative to the root storage path.
     */
    public function getPathForConversions(Media $media): string
    {
        return $this->getBasePath($media).'/conversions/';
    }

    /*
     * Get the path for responsive images of the given media, relative to the root storage path.
     */
    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getBasePath($media).'/responsive-images/';
    }

    /*
     * Get the base path for the given media.
     */
    protected function getBasePath(Media $media): string
    {
        $model = $media->model;

        if ($model instanceof Activity && $model->activity_number) {
            return "{$model->activity_number}/{$media->id}";
        }

        if ($model instanceof Gallery) {
            $activityNumber = $model->activity->activity_number ?? 'unknown';

            return "{$activityNumber}/galleries/{$model->id}/{$media->id}";
        }

        return "media/{$media->id}";
    }
}
