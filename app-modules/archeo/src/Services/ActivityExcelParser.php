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
            foreach ($dataRows as $rowIndex => $row) {
                // PHPSpreadsheet toArray index starts at 1, but if we did array_shift it's gone.
                // Using array_slice with true as 4th arg preserves keys.
                $activityNumber = $row['A'] ?? null;

                if (empty($activityNumber)) {
                    throw ExcelRowValidationException::missingActivityNumber($rowIndex);
                }

                $yearStr = $row['D'] ?? '';
                $years = $this->parseYears($yearStr, $rowIndex);

                Activity::query()->updateOrCreate(
                    ['activity_number' => $activityNumber],
                    [
                        'file_name' => $originalFileName,
                        'cvs_number' => (int) ($row['B'] ?? 0),
                        'registration_year' => (int) ($row['C'] ?? 0),
                        'activity_year_start' => $years['start'],
                        'activity_year_end' => $years['end'],
                        'activity_type' => $row['E'] ?? '',
                        'cadastral_area' => $row['F'] ?? null,
                        'municipality' => $row['G'] ?? null,
                        'position' => $row['H'] ?? null,
                        'district' => $row['I'] ?? null,
                        'research_leader' => $row['J'] ?? '',
                        'author_ns' => $row['K'] ?? '',
                        'institution' => $row['L'] ?? null,
                        'action_number' => $row['M'] ?? null,
                        'dating_ns' => $this->parseArray($row['N'] ?? null),
                        'dating_ceans' => $this->parseArray($row['O'] ?? null),
                        'site_type_original' => $row['P'] ?? null,
                        'dating_site_type' => $this->parseArray($row['Q'] ?? null),
                        'localization_degree' => (int) ($row['R'] ?? 0),
                        'has_gis_link' => filter_var($row['S'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'coordinate_x' => $row['T'] ?? null,
                        'coordinate_y' => $row['U'] ?? null,
                        'size_category' => $row['V'] ?? '',
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
            throw new ExcelRowValidationException($rowIndex, 'Activity year (Column D) is required.');
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
