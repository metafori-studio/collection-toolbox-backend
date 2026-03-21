<?php

namespace Metafori\Archeo\Exceptions;

class ExcelRowValidationException extends ActivityImportException
{
    public function __construct(int $rowNumber, string $message)
    {
        parent::__construct("Row {$rowNumber}: {$message}");
    }

    public static function missingActivityNumber(int $rowNumber): self
    {
        return new self($rowNumber, 'Missing activity number (Column A).');
    }

    public static function invalidYear(int $rowNumber, string $yearValue): self
    {
        return new self($rowNumber, "Invalid year format '{$yearValue}' in Column D. Expected 'YYYY' or 'YYYY-YYYY'.");
    }
}
