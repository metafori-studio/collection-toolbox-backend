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
     * @throws InvalidFileFormatException
     * @throws Exception
     */
    public function importFromPath(string $localPath, string $originalFileName): int
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
        $insertedCount = 0;

        DB::transaction(function () use ($dataRows, $originalFileName, &$insertedCount) {
            $mapping = config('archeo.import_mapping');

            foreach ($dataRows as $rowIndex => $row) {
                $activityNumber = $row[$mapping['activity_number'] ?? 'A'] ?? null;

                if (empty($activityNumber)) {
                    continue;
                }

                // Sanitize activity number to only include digits
                $activityNumber = preg_replace('/[^0-9]/', '', (string) $activityNumber);

                if (empty($activityNumber)) {
                    continue;
                }

                $yearStr = $row[$mapping['years'] ?? 'D'] ?? '';
                $years = $this->parseYears($yearStr, $rowIndex);

                Activity::query()->updateOrCreate(
                    ['activity_number' => $activityNumber],
                    [
                        'file_name' => $originalFileName,
                        'cvs_number' => (int) ($row[$mapping['cvs_number'] ?? 'B'] ?? 0),
                        'registration_year' => (int) ($row[$mapping['registration_year'] ?? 'C'] ?? 0),
                        'activity_year_start' => $years['start'],
                        'activity_year_end' => $years['end'],
                        'activity_type' => $row[$mapping['activity_type'] ?? 'E'] ?? '',
                        'cadastral_area' => $row[$mapping['cadastral_area'] ?? 'F'] ?? null,
                        'municipality' => $row[$mapping['municipality'] ?? 'G'] ?? null,
                        'position' => $row[$mapping['position'] ?? 'H'] ?? null,
                        'district' => $row[$mapping['district'] ?? 'I'] ?? null,
                        'research_leader' => $row[$mapping['research_leader'] ?? 'J'] ?? '',
                        'author_ns' => $row[$mapping['author_ns'] ?? 'K'] ?? '',
                        'institution' => $row[$mapping['institution'] ?? 'L'] ?? null,
                        'action_number' => $row[$mapping['action_number'] ?? 'M'] ?? null,
                        'dating_ns' => $this->parseArray($row[$mapping['dating_ns'] ?? 'N'] ?? null),
                        'dating_ceans' => $this->parseArray($row[$mapping['dating_ceans'] ?? 'O'] ?? null),
                        'site_type_original' => $row[$mapping['site_type_original'] ?? 'P'] ?? null,
                        'dating_site_type' => $this->parseArray($row[$mapping['dating_site_type'] ?? 'Q'] ?? null),
                        'localization_degree' => (int) ($row[$mapping['localization_degree'] ?? 'R'] ?? 0),
                        'has_gis_link' => filter_var($row[$mapping['has_gis_link'] ?? 'S'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'coordinate_x' => $row[$mapping['coordinate_x'] ?? 'T'] ?? null,
                        'coordinate_y' => $row[$mapping['coordinate_y'] ?? 'U'] ?? null,
                        'size_category' => $row[$mapping['size_category'] ?? 'V'] ?? '',
                    ]
                );
                $insertedCount++;
            }
        });

        return $insertedCount;
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
            $start = trim($parts[0]);
            $end = trim($parts[1]);

            if (! is_numeric($start) || ! is_numeric($end)) {
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
}
