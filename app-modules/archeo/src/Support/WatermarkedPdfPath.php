<?php

namespace Metafori\Archeo\Support;

use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\Support\PathGenerator\PathGeneratorFactory;

class WatermarkedPdfPath
{
    public static function forMedia(Media $media): string
    {
        $dir = PathGeneratorFactory::create($media)->getPathForConversions($media);
        $name = pathinfo($media->file_name, PATHINFO_FILENAME);

        return $dir.$name.'-watermarked.pdf';
    }
}
