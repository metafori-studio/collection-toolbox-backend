<?php

namespace Metafori\Archeo\Exceptions;

class InvalidFileFormatException extends ActivityImportException
{
    public static function unreadable(string $path): self
    {
        $fileName = basename($path);

        return new self(__('archeo::activities.import.errors.file_unreadable', [
            'filename' => $fileName,
        ]));
    }

    public static function empty(): self
    {
        return new self(__('archeo::activities.import.errors.file_empty'));
    }
}
