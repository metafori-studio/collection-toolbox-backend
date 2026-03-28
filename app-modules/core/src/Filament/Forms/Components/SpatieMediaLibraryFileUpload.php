<?php

namespace Metafori\Core\Filament\Forms\Components;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload as BaseSpatieMediaLibraryFileUpload;

class SpatieMediaLibraryFileUpload extends BaseSpatieMediaLibraryFileUpload
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->maxParallelUploads(config('file_upload.max_parallel_uploads'));
    }
}
