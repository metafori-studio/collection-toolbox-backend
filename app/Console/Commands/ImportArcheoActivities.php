<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Metafori\Archeo\Models\Activity;

class ImportArcheoActivities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'archeo:import-activities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import archeo activities from CSV';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $path = base_path('data.csv');
        if (! file_exists($path)) {
            $this->error('data.csv not found at '.$path);

            return;
        }

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        // Clean headers specifically in case of BOM or extra spaces
        $header = array_map(function ($h) {
            return trim($h, " \t\n\r\0\x0B\xEF\xBB\xBF");
        }, $header);

        $inserted = 0;
        $updated = 0;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                // If the row has fewer or more columns than header, pad/slice it
                if (count($row) < count($header)) {
                    $row = array_pad($row, count($header), null);
                } elseif (count($row) > count($header)) {
                    $row = array_slice($row, 0, count($header));
                }

                $data = array_combine($header, $row);

                // parse Rok aktivity
                $rokAktivity = $data['Rok aktivity'] ?? '';
                preg_match_all('/\d{4}/', $rokAktivity, $matches);
                if (! empty($matches[0])) {
                    $years = array_map('intval', $matches[0]);
                    $activity_year_start = min($years);
                    $activity_year_end = max($years);
                } else {
                    $activity_year_start = 0; // The column is not nullable
                    $activity_year_end = 0;   // The column is not nullable
                }

                // Helper closure for array parsing
                $parseArray = function ($string) {
                    if ($string === null || trim($string) === '') {
                        return null;
                    }

                    return array_values(array_filter(array_map('trim', explode(',', $string))));
                };

                // nullable helper
                $nullable = function ($val) {
                    return ($val === null || trim($val) === '') ? null : trim($val);
                };

                $gisVazba = strtoupper(trim($data['Existuje GIS väzba'] ?? ''));
                $has_gis_link = ($gisVazba === '1' || $gisVazba === 'TRUE');

                // Clean cvs_number to be integer safely
                $cvsNumber = (int) $nullable($data['ČVS']);

                $attributes = [
                    'cvs_number' => $cvsNumber,
                    'registration_year' => $nullable($data['Rok zaevidovania']),
                    'activity_year_start' => $activity_year_start,
                    'activity_year_end' => $activity_year_end,
                    'activity_type' => $nullable($data['Druh aktivity']) ?? 'Neznámy',
                    'cadastral_area' => $nullable($data['Katastrálne územie']),
                    'municipality' => $nullable($data['Obec']),
                    'position' => $nullable($data['Poloha']),
                    'district' => $nullable($data['Okres']),
                    'localization_degree' => $nullable($data['Stupeň lokalizácie']),
                    'coordinate_x' => $nullable($data['Súradnica X']),
                    'coordinate_y' => $nullable($data['Súradnica Y']),
                    'has_gis_link' => $has_gis_link,
                    'research_leader' => $nullable($data['Vedúci výskumu']) ?? 'Neznámy',
                    'author_ns' => $nullable($data['Autor - NS']) ?? 'Neznámy',
                    'institution' => $nullable($data['Inštitúcia']),
                    'action_number' => $nullable($data['Číslo akcie']),
                    'dating_ns' => $parseArray($data['Datovanie - NS'] ?? ''),
                    'dating_ceans' => $parseArray($data['Datovanie - CEANS'] ?? ''),
                    'site_type_original' => $parseArray($data['Druh náleziska - pôvodná hodnota'] ?? ''),
                    'dating_site_type' => $parseArray($data['Datovanie-druh náleziska'] ?? ''),
                    'size_category' => $nullable($data['Malá/veľká']) ?? 'Neznáma',
                ];

                $activityNumber = str_replace('č.a. ', '', $data['Číslo aktivity'] ?? '');
                $activityNumber = trim($activityNumber);

                /** @var \Metafori\Archeo\Models\Activity|null $activity */
                $activity = Activity::where('activity_number', $activityNumber)->first();
                if ($activity) {
                    $activity->update($attributes);
                    $updated++;
                } else {
                    $attributes['activity_number'] = $activityNumber;
                    Activity::create($attributes);
                    $inserted++;
                }
            }
            DB::commit();
            $this->info("Successfully imported {$inserted} / updated {$updated} activities.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error importing on row {$inserted} (updated {$updated}): ".$e->getMessage());
        } finally {
            fclose($handle);
        }
    }
}
