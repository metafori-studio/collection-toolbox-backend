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

        if ($model instanceof Activity) {
            $activityNumber = $this->sanitizeActivityNumber($model->activity_number);

            return "{$activityNumber}/{$media->id}";
        }

        if ($model instanceof Gallery) {
            $activityNumber = $this->sanitizeActivityNumber($model->activity->activity_number ?? null);

            return "{$activityNumber}/galleries/{$model->id}/{$media->id}";
        }

        return "media/{$media->id}";
    }

    /**
     * Sanitize activity number for filesystem safety.
     */
    protected function sanitizeActivityNumber(?string $activityNumber): string
    {
        if (empty($activityNumber)) {
            return 'unknown';
        }

        // Remove unsafe characters, allowing only A-Za-z0-9, underscore, and hyphen.
        // Also strip path separators and dots.
        $sanitized = preg_replace('/[^A-Za-z0-9_-]/', '-', $activityNumber);

        // Remove multiple consecutive hyphens
        $sanitized = preg_replace('/-+/', '-', $sanitized);

        // Trim hyphens from both ends
        $sanitized = trim($sanitized, '-');

        return ! empty($sanitized) ? $sanitized : 'unknown';
    }
}
