<?php

namespace Metafori\Archeo\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Metafori\Archeo\Exceptions\ExcelRowValidationException;
use Metafori\Archeo\Exceptions\InvalidFileFormatException;
use Metafori\Archeo\Models\Activity;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ActivityExcelParser
{
    protected const DEFAULT_IMPORT_MAPPING = [
        'activity_number' => 'A',
        'cvs_number' => 'B',
        'registration_year' => 'C',
        'years' => 'D',
        'activity_type' => 'E',
        'cadastral_area' => 'F',
        'municipality' => 'G',
        'position' => 'H',
        'district' => 'I',
        'research_leader' => 'J',
        'author_ns' => 'K',
        'institution' => 'L',
        'action_number' => 'M',
        'dating_ns' => 'N',
        'dating_ceans' => 'O',
        'site_type_original' => 'P',
        'dating_site_type' => 'Q',
        'localization_degree' => 'R',
        'has_gis_link' => 'S',
        'coordinate_x' => 'T',
        'coordinate_y' => 'U',
        'size_category' => 'V',
    ];

    /**
     * @return array{count: int, created: int, updated: int, errors: array}
     *
     * @throws InvalidFileFormatException
     * @throws Exception
     */
    public function importFromPath(string $localPath, int $importId): array
    {
        try {
            $spreadsheet = IOFactory::load($localPath);
        } catch (Exception $e) {
            throw InvalidFileFormatException::unreadable(basename($localPath));
        }

        $sheet = $spreadsheet->getSheet(0);
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) <= 1) {
            throw InvalidFileFormatException::empty();
        }

        // Use the default mapping configuration
        $mapping = self::DEFAULT_IMPORT_MAPPING;

        // Validate header columns
        $headerRow = $rows[1]; // First row is the header
        $missingColumns = [];

        foreach ($mapping as $fieldName => $expectedColumn) {
            $headerValue = $headerRow[$expectedColumn] ?? null;
            if (empty(trim($headerValue ?? ''))) {
                $missingColumns[] = "{$fieldName} (Column {$expectedColumn})";
            }
        }

        if (! empty($missingColumns)) {
            throw InvalidFileFormatException::invalidHeader($missingColumns);
        }

        // Keep track of original row indices (starting from 1)
        // Header is row 1, data starts at row 2
        $dataRows = array_slice($rows, 1, null, true);
        $createdCount = 0;
        $updatedCount = 0;
        $errors = [];
        $processedActivityNumbers = [];

        DB::transaction(function () use ($dataRows, $importId, &$createdCount, &$updatedCount, &$errors, &$processedActivityNumbers, $mapping) {
            $transformer = new CoordinateTransformer;

            foreach ($dataRows as $rowIndex => $row) {
                $result = $this->processRow($row, $rowIndex, $importId, $processedActivityNumbers, $transformer, $mapping);

                $createdCount += $result['created'];
                $updatedCount += $result['updated'];
                $errors = array_merge($errors, $result['errors']);
            }
        });

        return [
            'created' => $createdCount,
            'updated' => $updatedCount,
            'count' => $createdCount + $updatedCount,
            'errors' => $errors,
        ];
    }

    /**
     * Process a single row of activity data
     *
     * @return array{created: int, updated: int, errors: array, activityNumber: ?string}
     */
    private function processRow(array $row, int $rowIndex, int $importId, array &$processedActivityNumbers, CoordinateTransformer $transformer, array $mapping): array
    {
        $created = 0;
        $updated = 0;
        $rowErrors = [];
        $activityNumber = null;

        try {
            // Silently skip completely empty rows
            if (empty(array_filter($row, fn ($cell) => ! is_null($cell) && $cell !== ''))) {
                return ['created' => 0, 'updated' => 0, 'errors' => [], 'activityNumber' => null];
            }

            $activityNumber = $row[$mapping['activity_number']] ?? null;

            if (empty($activityNumber)) {
                throw ExcelRowValidationException::missingActivityNumber($rowIndex);
            }

            // Sanitize activity number to only include digits
            $activityNumber = preg_replace('/[^0-9]/', '', (string) $activityNumber);

            if (empty($activityNumber)) {
                throw ExcelRowValidationException::invalidActivityNumber($rowIndex);
            }

            // Check if we already processed this activity number in current import
            if (in_array($activityNumber, $processedActivityNumbers)) {
                throw ExcelRowValidationException::duplicateActivityNumber($rowIndex);
            }

            $yearStr = $row[$mapping['years']] ?? '';
            $years = $this->parseYears($yearStr, $rowIndex);

            $coordinateX = $this->toNullableFloat($row[$mapping['coordinate_x']] ?? null);
            $coordinateY = $this->toNullableFloat($row[$mapping['coordinate_y']] ?? null);
            $latitude = null;
            $longitude = null;

            if ($coordinateX !== null && $coordinateY !== null) {
                $transformed = $transformer->sjtskToWgs84($coordinateX, $coordinateY);
                if ($transformed) {
                    $latitude = $transformed['latitude'];
                    $longitude = $transformed['longitude'];
                } else {
                    $rowErrors[] = "Row {$rowIndex} (Activity: {$activityNumber}): Failed to transform JTSK coordinates ($coordinateX, $coordinateY) to WGS84.";
                }
            }

            $activity = Activity::query()->updateOrCreate(
                ['activity_number' => $activityNumber],
                [
                    'import_id' => $importId,
                    'cvs_number' => $this->toNullableIntIfNumeric($row[$mapping['cvs_number']] ?? null),
                    'registration_year' => $this->toNullableInt($row[$mapping['registration_year']] ?? null),
                    'activity_year_start' => $years['start'],
                    'activity_year_end' => $years['end'],
                    'activity_type' => $row[$mapping['activity_type']] ?? '',
                    'cadastral_area' => $row[$mapping['cadastral_area']] ?? null,
                    'municipality' => $row[$mapping['municipality']] ?? null,
                    'position' => $row[$mapping['position']] ?? null,
                    'district' => $row[$mapping['district']] ?? null,
                    'research_leader' => $row[$mapping['research_leader']] ?? '',
                    'author_ns' => $this->parseArray($row[$mapping['author_ns']] ?? null),
                    'institution' => $row[$mapping['institution']] ?? null,
                    'action_number' => $row[$mapping['action_number']] ?? null,
                    'dating_ns' => $this->parseArray($row[$mapping['dating_ns']] ?? null),
                    'dating_ceans' => $this->parseArray($row[$mapping['dating_ceans']] ?? null),
                    'site_type_original' => $row[$mapping['site_type_original']] ?? null,
                    'dating_site_type' => $this->parseArray($row[$mapping['dating_site_type']] ?? null),
                    'localization_degree' => $this->toNullableInt($row[$mapping['localization_degree']] ?? null),
                    'has_gis_link' => filter_var($row[$mapping['has_gis_link']] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'coordinate_x' => $coordinateX,
                    'coordinate_y' => $coordinateY,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'size_category' => $row[$mapping['size_category']] ?? '',
                ]
            );

            if ($activity->wasRecentlyCreated) {
                $created = 1;
            } else {
                $updated = 1;
            }

            $processedActivityNumbers[] = $activityNumber;
        } catch (Exception $e) {
            $id = $activityNumber ?? 'Unknown';
            $rowErrors[] = "Row {$rowIndex} (Activity: {$id}): ".$e->getMessage();
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'errors' => $rowErrors,
            'activityNumber' => $activityNumber,
        ];
    }

    /**
     * @throws ExcelRowValidationException
     */
    protected function parseYears(string $yearStr, int $rowIndex): array
    {
        $yearStr = (string) trim($yearStr);

        if (empty($yearStr)) {
            throw new ExcelRowValidationException($rowIndex, __('archeo::activities.import.errors.year_required'));
        }

        // Handle comma-separated years (e.g., "2022, 2023")
        if (str_contains($yearStr, ',')) {
            $years = array_map('trim', explode(',', $yearStr));
            $years = array_filter($years, 'is_numeric');

            if (empty($years)) {
                throw ExcelRowValidationException::invalidYear($rowIndex, $yearStr);
            }

            return [
                'start' => (int) min($years),
                'end' => (int) max($years),
            ];
        }

        if (str_contains($yearStr, '-')) {
            $parts = explode('-', $yearStr);

            if (count($parts) !== 2) {
                throw ExcelRowValidationException::invalidYear($rowIndex, $yearStr);
            }

            $start = trim($parts[0]);
            $end = trim($parts[1]);

            if ($start === '' || $end === '' || ! is_numeric($start) || ! is_numeric($end)) {
                throw ExcelRowValidationException::invalidYear($rowIndex, $yearStr);
            }

            return [
                'start' => (int) $start,
                'end' => (int) $end,
            ];
        }

        if (! is_numeric($yearStr)) {
            throw ExcelRowValidationException::invalidYear($rowIndex, $yearStr);
        }

        $year = (int) $yearStr;

        return ['start' => $year, 'end' => $year];
    }

    protected function parseArray(?string $value): ?array
    {
        if (! $value) {
            return null;
        }

        return array_map('trim', explode(',', $value));
    }

    protected function toNullableInt(mixed $value): ?int
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    protected function toNullableIntIfNumeric(mixed $value): ?int
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    protected function toNullableFloat(mixed $value): ?float
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }
}
