<?php

namespace Metafori\Archeo\Exceptions;

class InvalidFileFormatException extends ActivityImportException
{
    public static function unreadable(string $fileName): self
    {
        return new self(__('archeo::activities.import.errors.file_unreadable', [
            'filename' => $fileName,
        ]));
    }

    public static function empty(): self
    {
        return new self(__('archeo::activities.import.errors.file_empty'));
    }

    public static function invalidHeader(array $missingColumns): self
    {
        $missingList = implode(', ', $missingColumns);

        return new self(__('archeo::activities.import.errors.invalid_header', [
            'columns' => $missingList,
        ]));
    }
}
