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
    /**
     * @deprecated Config-based mapping is deprecated. Use direct mapping in this class instead.
     */
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
            throw InvalidFileFormatException::unreadable($localPath);
        }

        $sheet = $spreadsheet->getSheet(0);
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) <= 1) {
            throw InvalidFileFormatException::empty();
        }

        // Keep track of original row indices (starting from 1)
        // Header is row 1, data starts at row 2
        $dataRows = array_slice($rows, 1, null, true);
        $createdCount = 0;
        $updatedCount = 0;
        $errors = [];
        $processedActivityNumbers = [];

        DB::transaction(function () use ($dataRows, $importId, &$createdCount, &$updatedCount, &$errors, &$processedActivityNumbers) {
            $mapping = config('archeo.import_mapping', self::DEFAULT_IMPORT_MAPPING);
            $transformer = new CoordinateTransformer;

            foreach ($dataRows as $rowIndex => $row) {
                try {
                    // Silently skip completely empty rows
                    if (empty(array_filter($row, fn ($cell) => ! is_null($cell) && $cell !== ''))) {
                        continue;
                    }

                    $activityNumber = $row[$mapping['activity_number'] ?? 'A'] ?? null;

                    if (empty($activityNumber)) {
                        $errors[] = "Row {$rowIndex}: Missing activity number.";

                        continue;
                    }

                    // Sanitize activity number to only include digits
                    $activityNumber = preg_replace('/[^0-9]/', '', (string) $activityNumber);

                    if (empty($activityNumber)) {
                        $errors[] = "Row {$rowIndex}: Invalid activity number format.";

                        continue;
                    }

                    // Check if we already processed this activity number in current import
                    if (in_array($activityNumber, $processedActivityNumbers)) {
                        $errors[] = "Row {$rowIndex} (Activity: {$activityNumber}): Duplicate activity number in this file. Skipping.";

                        continue;
                    }

                    $yearStr = $row[$mapping['years'] ?? 'D'] ?? '';
                    $years = $this->parseYears($yearStr, $rowIndex);

                    $coordinateX = $row[$mapping['coordinate_x'] ?? 'T'] ?? null;
                    $coordinateY = $row[$mapping['coordinate_y'] ?? 'U'] ?? null;
                    $latitude = null;
                    $longitude = null;

                    if ($coordinateX !== null && $coordinateY !== null && is_numeric($coordinateX) && is_numeric($coordinateY)) {
                        $transformed = $transformer->sjtskToWgs84((float) $coordinateX, (float) $coordinateY);
                        if ($transformed) {
                            $latitude = $transformed['latitude'];
                            $longitude = $transformed['longitude'];
                        } else {
                            $errors[] = "Row {$rowIndex} (Activity: {$activityNumber}): Failed to transform JTSK coordinates ($coordinateX, $coordinateY) to WGS84.";
                        }
                    }

                    $activity = Activity::query()->updateOrCreate(
                        ['activity_number' => $activityNumber],
                        [
                            'import_id' => $importId,
                            'cvs_number' => $this->toNullableInt($row[$mapping['cvs_number'] ?? 'B'] ?? null, true),
                            'registration_year' => $this->toNullableInt($row[$mapping['registration_year'] ?? 'C'] ?? null),
                            'activity_year_start' => $years['start'],
                            'activity_year_end' => $years['end'],
                            'activity_type' => $row[$mapping['activity_type'] ?? 'E'] ?? '',
                            'cadastral_area' => $row[$mapping['cadastral_area'] ?? 'F'] ?? null,
                            'municipality' => $row[$mapping['municipality'] ?? 'G'] ?? null,
                            'position' => $row[$mapping['position'] ?? 'H'] ?? null,
                            'district' => $row[$mapping['district'] ?? 'I'] ?? null,
                            'research_leader' => $row[$mapping['research_leader'] ?? 'J'] ?? '',
                            'author_ns' => $this->parseArray($row[$mapping['author_ns'] ?? 'K'] ?? null),
                            'institution' => $row[$mapping['institution'] ?? 'L'] ?? null,
                            'action_number' => $row[$mapping['action_number'] ?? 'M'] ?? null,
                            'dating_ns' => $this->parseArray($row[$mapping['dating_ns'] ?? 'N'] ?? null),
                            'dating_ceans' => $this->parseArray($row[$mapping['dating_ceans'] ?? 'O'] ?? null),
                            'site_type_original' => $row[$mapping['site_type_original'] ?? 'P'] ?? null,
                            'dating_site_type' => $this->parseArray($row[$mapping['dating_site_type'] ?? 'Q'] ?? null),
                            'localization_degree' => $this->toNullableInt($row[$mapping['localization_degree'] ?? 'R'] ?? null),
                            'has_gis_link' => filter_var($row[$mapping['has_gis_link'] ?? 'S'] ?? false, FILTER_VALIDATE_BOOLEAN),
                            'coordinate_x' => $coordinateX,
                            'coordinate_y' => $coordinateY,
                            'latitude' => $latitude,
                            'longitude' => $longitude,
                            'size_category' => $row[$mapping['size_category'] ?? 'V'] ?? '',
                        ]
                    );

                    if ($activity->wasRecentlyCreated) {
                        $createdCount++;
                    } else {
                        $updatedCount++;
                    }

                    $processedActivityNumbers[] = $activityNumber;
                } catch (Exception $e) {
                    $id = $activityNumber ?? 'Unknown';
                    $errors[] = "Row {$rowIndex} (Activity: {$id}): ".$e->getMessage();
                }
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

    protected function toNullableInt(mixed $value, bool $validateNumeric = false): ?int
    {
        if (is_string($value)) {
            $value = trim($value);
        }

        if ($value === null || $value === '') {
            return null;
        }

        if ($validateNumeric && ! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }
}
