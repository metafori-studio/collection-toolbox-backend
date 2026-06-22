<?php

namespace Metafori\Archeo\Support;

class WatermarkImage
{
    /**
     * Whether the configured watermark image is usable: an HTTPS URL or a local
     * file that exists on disk.
     */
    public static function isUsable(?string $image): bool
    {
        if ($image === null || $image === '') {
            return false;
        }

        return str_starts_with($image, 'https://') || is_file($image);
    }
}
