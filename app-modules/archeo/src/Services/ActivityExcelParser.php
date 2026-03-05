<?php

namespace Metafori\Archeo\Services;

use Illuminate\Support\Facades\DB;
use Metafori\Archeo\Models\Activity;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ActivityExcelParser
{
    public function importFromPath(string $localPath, string $originalFileName): int
    {
        $spreadsheet = IOFactory::load($localPath);
        $sheet = $spreadsheet->getSheet(0);
        $rows = $sheet->toArray(null, true, true, true);

        // Remove header row
        array_shift($rows);

        $insertedCount = 0;

        DB::transaction(function () use ($rows, $originalFileName, &$insertedCount) {
            foreach ($rows as $row) {
                $activityNumber = $row['A'] ?? null;

                if (empty($activityNumber)) {
                    continue;
                }

                $years = $this->parseYears($row['D'] ?? '');

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

    protected function parseYears(string $yearStr): array
    {
        $yearStr = (string) $yearStr;
        if (str_contains($yearStr, '-')) {
            $parts = explode('-', $yearStr);

            return [
                'start' => (int) trim($parts[0]),
                'end' => (int) trim($parts[1]),
            ];
        }

        $year = (int) trim($yearStr);

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
