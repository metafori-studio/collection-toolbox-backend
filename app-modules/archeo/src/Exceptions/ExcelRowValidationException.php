<?php

namespace Metafori\Archeo\Exceptions;

class ExcelRowValidationException extends ActivityImportException
{
    public function __construct(int $rowNumber, string $message)
    {
        parent::__construct(__('archeo::activities.import.row_error', [
            'row' => $rowNumber,
            'message' => $message,
        ]));
    }

    public static function missingActivityNumber(int $rowNumber): self
    {
        return new self($rowNumber, __('archeo::activities.import.errors.missing_activity_number'));
    }

    public static function invalidYear(int $rowNumber, string $yearValue): self
    {
        return new self($rowNumber, __('archeo::activities.import.errors.invalid_year', ['value' => $yearValue]));
    }
}
