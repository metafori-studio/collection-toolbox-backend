<?php

return [
    'media_disk' => env('ARCHEO_GALLERIES_DISK', 'public'),

    'pdfs_disk' => env('ARCHEO_PDFS_DISK', 'public'),

    'ghostscript_binary' => env('ARCHEO_GHOSTSCRIPT_BINARY', 'gs'),

    'magick_binary' => env('ARCHEO_MAGICK_BINARY', 'magick'),

    'qpdf_binary' => env('ARCHEO_QPDF_BINARY', 'qpdf'),

    // Absolute path or HTTPS URL to the watermark image. Leave null to disable watermarking.
    'watermark_image' => env('ARCHEO_WATERMARK_IMAGE'),
];
