<?php

namespace Metafori\Archeo\Exceptions;

class InvalidFileFormatException extends ActivityImportException
{
    public static function unreadable(string $path): self
    {
        $fileName = basename($path);

        return new self("The file '{$fileName}' is unreadable or not a valid Excel file.");
    }

    public static function empty(): self
    {
        return new self('The uploaded Excel file contains no data.');
    }
}
