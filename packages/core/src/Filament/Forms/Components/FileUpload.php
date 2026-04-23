<?php

namespace Metafori\Core\Filament\Forms\Components;

use Filament\Forms\Components\FileUpload as BaseFileUpload;

class FileUpload extends BaseFileUpload
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->maxParallelUploads(config('file-upload.max_parallel_uploads'));
    }
}
