<?php

namespace Metafori\Core\Database\Seeders\Locality;

use Illuminate\Database\Seeder;
use Metafori\Core\Models\Country;
use Metafori\Core\Models\District;
use Metafori\Core\Models\Municipality;
use Metafori\Core\Models\Region;

class SlovakiaSeeder extends Seeder
{
    public function run(): void
    {
        $country = Country::firstOrCreate(
            ['name->sk' => 'Slovensko'],
            ['name' => ['sk' => 'Slovensko']]
        );

        $csvPath = __DIR__.'/data/sk.csv';
        $handle = fopen($csvPath, 'r');

        $regionsMap = [];
        $districtsMap = [];
        $municipalitiesMap = [];

        try {
            while (($data = fgetcsv($handle, separator: ';')) !== false) {
                [$regionName, $districtName, $municipalityName] = $data;

                if (! isset($regionsMap[$regionName])) {
                    $region = Region::firstOrCreate(
                        [
                            'country_id' => $country->id,
                            'name->sk' => $regionName,
                        ],
                        [
                            'name' => ['sk' => $regionName],
                        ]
                    );
                    $regionsMap[$regionName] = $region->id;
                }
                $regionId = $regionsMap[$regionName];

                $districtKey = "$regionId|$districtName";
                if (! isset($districtsMap[$districtKey])) {
                    $district = District::firstOrCreate(
                        [
                            'region_id' => $regionId,
                            'name->sk' => $districtName,
                        ],
                        [
                            'name' => ['sk' => $districtName],
                        ]
                    );
                    $districtsMap[$districtKey] = $district->id;
                }
                $districtId = $districtsMap[$districtKey];

                $municipalityKey = "$districtId|$municipalityName";
                if (! isset($municipalitiesMap[$municipalityKey])) {
                    $municipality = Municipality::firstOrCreate(
                        [
                            'district_id' => $districtId,
                            'name->sk' => $municipalityName,
                        ],
                        [
                            'name' => ['sk' => $municipalityName],
                        ]
                    );
                    $municipalitiesMap[$municipalityKey] = $municipality->id;
                }
            }
            $this->command->info('Localities imported successfully!');
        } catch (\Exception $e) {
            $this->command->error('An error occurred during import: '.$e->getMessage());
        }

        if (\is_resource($handle)) {
            fclose($handle);
        }
    }
}
